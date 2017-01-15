<?php namespace lang;

use lang\archive\ArchiveClassLoader;
use lang\reflect\Module;

/** 
 * Entry point class to loading classes, packages and resources.
 *
 * Keeps a list of class loaders that load classes from the file system,
 * xar archives, memory, or various other places. These loaders are asked
 * for each class loading request, be it via XPClass::forName(), SPL auto
 * loading, requests from the lang.reflect.Package class, or explicit calls
 * to loadClass().
 *
 * Given the following code
 * ```php
 * $class= ClassLoader::getDefault()->loadClass($name);
 * ```
 * ...and `.:/usr/local/lib/xp/xp-rt-6.4.0.xar:/home/classes/` set as 
 * PHP's include path, the classloader will ask the class loader delegates:
 * 
 * - FileSystemClassLoader(.)
 * - ArchiveClassLoader(/usr/local/lib/xp/xp-rt-6.4.0.xar)
 * - FileSystemClassLoader(/home/classes/)
 *
 * ...in the stated order. The first delegate to provide the class 
 * will be asked to load it. In case none of the delegates are able
 * to provide the class, a ClassNotFoundException will be thrown.
 * 
 * @test  xp://net.xp_framework.unittest.reflection.ClassLoaderTest
 * @test  xp://net.xp_framework.unittest.reflection.ClassPathTest
 * @test  xp://net.xp_framework.unittest.reflection.ResourcesTest
 * @test  xp://net.xp_framework.unittest.reflection.PackageTest
 * @test  xp://net.xp_framework.unittest.reflection.RuntimeClassDefinitionTest
 * @test  xp://net.xp_framework.unittest.reflection.FullyQualifiedTest
 * @test  xp://net.xp_framework.unittest.reflection.ModuleLoadingTest
 * @see   xp://lang.XPClass#forName
 * @see   xp://lang.reflect.Package#loadClass
 */
final class ClassLoader extends Object implements IClassLoader {
  protected static
    $delegates = [],
    $modules   = [];

  /**
   * Scan include-path, setting up classloaders for each unique element
   *
   * @return void
   */
  public static function bootstrap() {
    foreach (\xp::$classpath as $element) {
      if (isset(self::$delegates[$element])) {
        continue;
      } else if (is_dir($element)) {
        self::$delegates[$element]= FileSystemClassLoader::instanceFor($element, false);
      } else if (is_file($element)) {
        self::$delegates[$element]= ArchiveClassLoader::instanceFor($element, false);
      }
    }

    // Run deferred initialization
    \xp::$loader= new self();
    foreach (self::$delegates as $id => $delegate) {
      self::initialize($id, $delegate);
    }
  }

  /**
   * Initialize a classloader delegate: Loads autoload.php if not already
   * required (because loaded via Composer).
   *
   * @param  string $id
   * @param  lang.IClassLoader $cl
   */
  private static function initialize($id, $cl) {
    if ($cl->providesResource('/autoload.php')) {
      require_once($cl->getResourceAsStream('/autoload.php')->getURI());
    }

    // BC: If autoload.php did not define a module, check module.xp
    if (!isset(self::$modules[$id])) {
      self::$modules[$id]= Module::$INCOMPLETE;
      if ($cl->providesResource('module.xp')) {
        self::$modules[$id]= Module::register(self::declareModule($cl));
      }
    }
  }

  /**
   * Retrieve the default class loader
   *
   * @return  lang.ClassLoader
   */
  public static function getDefault() {
    return \xp::$loader;
  }

  /**
   * Register a class loader from a path
   *
   * @param  string $element
   * @param  bool $before default FALSE whether to register this as the first loader,
   *         NULL wheather to figure out position by inspecting $element
   * @param  string $module
   * @param  [:var] $definition
   * @return lang.IClassLoader the registered loader
   * @throws lang.ElementNotFoundException if the path cannot be found
   */
  public static function registerPath($element, $before= false, $module= null, $definition= []) {
    if (null === $before && '!' === $element{0}) {
      $before= true;
      $element= substr($element, 1);
    } else {
      $before= (bool)$before;
    }

    if (is_dir($element)) {
      return self::registerLoader(FileSystemClassLoader::instanceFor($element), $before, $module, $definition);
    } else if (is_file($element)) {
      return self::registerLoader(ArchiveClassLoader::instanceFor($element), $before, $module, $definition);
    }
    throw new ElementNotFoundException('Element "'.$element.'" not found');
  }
  
  /**
   * Register a class loader as a delegate
   *
   * @param  lang.IClassLoader $l
   * @param  bool $before default FALSE whether to register this as the first loader
   * @param  string $module
   * @param  [:var] $definition
   * @return lang.IClassLoader the registered loader
   */
  public static function registerLoader(IClassLoader $l, $before= false, $module= null, $definition= []) {
    $id= $l->instanceId();
    if ($before) {
      self::$delegates= array_merge([$id => $l], self::$delegates);
    } else {
      self::$delegates[$id]= $l;
    }

    if (!isset(self::$modules[$id])) {
      try {
        if ($module) {
          self::$modules[$id]= Module::$INCOMPLETE;
          self::$modules[$id]= Module::register(new Module($module, $l, $definition));
        } else {
          self::initialize($id, $l);
        }
      } catch (Throwable $e) {
        unset(self::$delegates[$id], self::$modules[$id]);
        throw $e;
      }
    }
    return $l;
  }

  /**
   * Declare a module
   *
   * @param   lang.IClassLoader l
   * @return  lang.reflect.Module
   */
  public static function declareModule($l) {
    $moduleInfo= $l->getResource('module.xp');
    if (!preg_match('/module ([a-z_\/\.-]+)(.*){/', $moduleInfo, $m)) {
      throw new ElementNotFoundException('Missing or malformed module-info in '.$l->toString());
    }

    $decl= strtr($m[1], '.-/', '___').'Module';
    if (preg_match('/namespace ([^;]+)/', $moduleInfo, $n)) {
      $class= strtr($n[1], '\\', '.').'.'.$decl;
    } else {
      $class= $decl;
    }

    if (strstr($m[2], 'extends')) {
      $parent= $m[2];
    } else {
      $parent= ' extends \lang\reflect\Module '.$m[2];
    }

    $dyn= DynamicClassLoader::instanceFor('modules');
    $dyn->setClassBytes($class, strtr($moduleInfo, [
      $m[0] => 'class '.$decl.$parent.'{',
      '<?php' => '', '?>' => ''
    ]));
    return $dyn->loadClass($class)->newInstance($m[1], $l);
  }

  /**
   * Unregister a class loader as a delegate
   *
   * @param   lang.IClassLoader l
   * @return  bool TRUE if the delegate was unregistered
   */
  public static function removeLoader(IClassLoader $l) {
    $id= $l->instanceId();
    if (isset(self::$delegates[$id])) {
      if (Module::$INCOMPLETE !== self::$modules[$id]) {
        Module::remove(self::$modules[$id]);
      }
      unset(self::$delegates[$id], self::$modules[$id]);
      return true;
    }
    return false;
  }

  /**
   * Get class loader delegates
   *
   * @return  lang.IClassLoader[]
   */
  public static function getLoaders() {
    return array_values(self::$delegates);
  }

  /**
   * Helper method to turn a given value into a literal
   *
   * @param  var class either an XPClass instance or a string
   * @return string
   */
  protected static function classLiteral($class) {
    if ($class instanceof XPClass) {
      return '\\'.$class->literal();
    } else if ('\\' === $class{0}) {
      return $class;
    } else {
      return '\\'.XPClass::forName($class)->literal();
    }
  }

  /**
   * Define a forward to a given function
   *
   * @param  string $name
   * @param  php.ReflectionParameter[] $params
   * @param  string $invoke
   */
  protected static function defineForward($name, $params, $invoke, $offset= 2) {
    $pass= $sig= '';
    foreach ($params as $param) {
      $p= $param->getName();
      if ($param->isArray()) {
        $sig.= ', array $'.$p;
      } else if ($param->isCallable()) {
        $sig.= ', callable $'.$p;
      } else if (null !== ($restriction= $param->getClass())) {
        $sig.= ', \\'.$restriction->getName().' $'.$p;
      } else if ($param->isVariadic()) {
        $sig.= ', ...$'.$p;
        $pass.= ', ...$'.$p;
        continue;
      } else {
        $sig.= ', $'.$p;
      }
      if ($param->isOptional()) {
        $sig.= '= '.var_export($param->getDefaultValue(), true);
      }
      $pass.= ', $'.$p;
    }

    $decl= 'function '.$name.'('.substr($sig, 2).')';
    if (null === $invoke) {
      return $decl.';';
    } else {
      return $decl.'{'.sprintf($invoke, substr($pass, $offset)).'}';
    }
  }

  /**
   * Helper method for defineClass() and defineInterface().
   *
   * @param  string $spec
   * @param  [:var] $declaration
   * @param  var $def
   * @return lang.XPClass
   */
  public static function defineType($spec, $declaration, $def) {
    if ('#' === $spec{0}) {
      $p= strrpos($spec, ' ');
      $typeAnnotations= substr($spec, 0, $p)."\n";
      $spec= substr($spec, $p+ 1);
    } else {
      $typeAnnotations= '';
    }

    if (isset(\xp::$cl[$spec])) return new XPClass(literal($spec));

    $functions= [];
    if (is_array($def)) {
      $iface= 'interface' === $declaration['kind'];
      $bytes= '';
      foreach ($def as $name => $member) {
        if ('#' === $name{0}) {
          $p= strrpos($name, ' ');
          $memberAnnotations= substr($name, 0, $p)."\n";
          $name= substr($name, $p+ 1);
        } else {
          $memberAnnotations= '';
        }

        if ($member instanceof \Closure) {
          $bytes.= $memberAnnotations.self::defineForward(
            $name,
            (new \ReflectionFunction($member))->getParameters(),
            $iface ? null : 'return self::$__func["'.$name.'"]->call($this%s);',
            0
          );
          $iface || $functions[$name]= $member;
        } else {
          $bytes.= $memberAnnotations.'public $'.$name.'= '.var_export($member, true).';';
        }
      }

      $iface || $bytes= 'static $__func= []; '.$bytes;
    } else {
      $bytes= substr(trim($def), 1, -1);
    }

    if (false !== ($p= strrpos($spec, '.'))) {
      $header= 'namespace '.strtr(substr($spec, 0, $p), '.', '\\').';';
      $name= substr($spec, $p + 1);
    } else if (false !== ($p= strrpos($spec, '\\'))) {
      $header= 'namespace '.substr($spec, 0, $p).';';
      $name= substr($spec, $p + 1);
      $spec= strtr($spec, '\\', '.');
    } else {
      $header= '';
      $name= $spec;
      \xp::$cn[$name]= $name;
    }

    if (isset($declaration['imports'])) {
      foreach ($declaration['imports'] as $class => $alias) {
        $header.= 'use '.substr(self::classLiteral($class), 1).($alias ? ' as '.$alias : '').';';
      }
    }

    $dyn= self::registerLoader(DynamicClassLoader::instanceFor(__METHOD__));
    $dyn->setClassBytes($spec, $x= sprintf(
      '%s%s%s %s %s%s%s {%s%s}',
      $header,
      $typeAnnotations,
      $declaration['modifiers'] ?? '',
      $declaration['kind'],
      $name,
      $declaration['extends'] ? ' extends '.implode(', ', array_map('self::classLiteral', $declaration['extends'])) : '',
      $declaration['implements'] ? ' implements '.implode(', ', array_map('self::classLiteral', $declaration['implements'])) : '',
      $declaration['use'] ? ' use '.implode(', ', array_map('self::classLiteral', $declaration['use'])).';' : '',
      $bytes
    ));
    $cl= $dyn->loadClass($spec);
    $functions && $cl->reflect()->setStaticPropertyValue('__func', $functions);
    return $cl;
  }

  /**
   * Define a class with a given name
   *
   * @param   string spec fully qualified class name, optionally prepended by annotations
   * @param   var parent The parent class either by qualified name or XPClass instance
   * @param   var[] interfaces The implemented interfaces either by qualified names or XPClass instances
   * @param   var $def Code
   * @return  lang.XPClass
   * @throws  lang.FormatException in case the class cannot be defined
   */
  public static function defineClass($spec, $parent, $interfaces, $def= null) {
    $decl= [
      'kind'       => 'class',
      'extends'    => [$parent],
      'implements' => (array)$interfaces,
      'use'        => []
    ];
    return self::defineType($spec, $decl, $def);
  }
  
  /**
   * Define an interface with a given name
   *
   * @param   string spec fully qualified class name, optionally prepended by annotations
   * @param   var[] parents The parent interfaces either by qualified names or XPClass instances
   * @param   var $def Code
   * @return  lang.XPClass
   * @throws  lang.FormatException in case the class cannot be defined
   */
  public static function defineInterface($spec, $parents, $def= null) {
    $decl= [
      'kind'       => 'interface',
      'extends'    => (array)$parents,
      'implements' => [],
      'use'        => []
    ];
    return self::defineType($spec, $decl, $def);
  }

  /**
   * Loads a class
   *
   * @param   string class fully qualified class name
   * @return  string class name of class loaded
   * @throws  lang.ClassNotFoundException in case the class can not be found
   * @throws  lang.ClassFormatException in case the class format is invalud
   */
  public function loadClass0($class) {
    if (isset(\xp::$cl[$class])) return literal($class);
    
    // Ask delegates
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesClass($class)) return $delegate->loadClass0($class);
    }
    throw new ClassNotFoundException($class, self::getLoaders());
  }

  /**
   * Checks whether this loader can provide the requested class
   *
   * @param   string class
   * @return  bool
   */
  public function providesClass($class) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesClass($class)) return true;
    }
    return false;
  }

  /**
   * Checks whether this loader can provide the requested URI as a class
   *
   * @param   string uri
   * @return  bool
   */
  public function providesUri($uri) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesUri($uri)) return true;
    }
    return false;
  }
  
  /**
   * Checks whether this loader can provide the requested resource
   *
   * @param   string filename
   * @return  bool
   */
  public function providesResource($filename) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesResource($filename)) return true;
    }
    return false;
  }

  /**
   * Checks whether this loader can provide the requested package
   *
   * @param   string package
   * @return  bool
   */
  public function providesPackage($package) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesPackage($package)) return true;
    }
    return false;
  }

  /**
   * Find the class by the specified name
   *
   * @param   string class fully qualified class name
   * @return  lang.IClassLoader the classloader that provides this class
   */
  public function findClass($class) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesClass($class)) return $delegate;
    }
    return null;
  }

  /**
   * Find the class by the specified URI
   *
   * @param   string uri
   * @return  lang.IClassLoader the classloader that provides this uri
   */
  public function findUri($uri) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesUri($uri)) return $delegate;
    }
    return null;
  }

  /**
   * Find the package by the specified name
   *
   * @param   string package fully qualified package name
   * @return  lang.IClassLoader the classloader that provides this class
   */
  public function findPackage($package) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesPackage($package)) return $delegate;
    }
    return null;
  }    
  
  /**
   * Load the class by the specified name
   *
   * @param   string class fully qualified class name
   * @return  lang.XPClass
   * @throws  lang.ClassNotFoundException in case the class can not be found
   */
  public function loadClass($class) {
    return new XPClass($this->loadClass0($class));
  }    

  /**
   * Find the resource by the specified name
   *
   * @param   string name resource name
   * @return  lang.IClassLoader the classloader that provides this resource
   */
  public function findResource($name) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesResource($name)) return $delegate;
    }
    return null;
  }    

  /**
   * Loads a resource.
   *
   * @param   string string name of resource
   * @return  string
   * @throws  lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResource($string) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesResource($string)) return $delegate->getResource($string);
    }
    throw new ElementNotFoundException(sprintf(
      'No classloader provides resource "%s" {%s}',
      $string,
      \xp::stringOf(self::getLoaders())
    ));
  }
  
  /**
   * Retrieve a stream to the resource
   *
   * @param   string string name of resource
   * @return  io.Stream
   * @throws  lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResourceAsStream($string) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesResource($string)) return $delegate->getResourceAsStream($string);
    }
    throw new ElementNotFoundException(sprintf(
      'No classloader provides resource "%s" {%s}',
      $string,
      \xp::stringOf(self::getLoaders())
    ));
  }

  /**
   * Find the class by a given URI
   *
   * @param   string uri
   * @return  lang.XPClass
   * @throws  lang.ClassNotFoundException in case the class can not be found
   */
  public function loadUri($uri) {
    foreach (self::$delegates as $delegate) {
      if ($delegate->providesUri($uri)) return $delegate->loadUri($uri);
    }
    throw new ClassNotFoundException('URI:'.$uri, self::getLoaders());
  }

  /**
   * Get package contents
   *
   * @param   string package
   * @return  string[] filenames
   */
  public function packageContents($package) {
    $contents= [];
    foreach (self::$delegates as $delegate) {
      $contents= array_merge($contents, $delegate->packageContents($package));
    }
    return array_unique($contents);
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return nameof($this);
  }

  /**
   * Returns a unique identifier for this class loader instance
   *
   * @return  string
   */
  public function instanceId() {
    return '*';
  }
}