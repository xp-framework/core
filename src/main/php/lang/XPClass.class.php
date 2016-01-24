<?php namespace lang;

use lang\reflect\Method;
use lang\reflect\Field;
use lang\reflect\Constructor;
use lang\reflect\Package;
use lang\ElementNotFoundException;

define('DETAIL_ARGUMENTS',      1);
define('DETAIL_RETURNS',        2);
define('DETAIL_THROWS',         3);
define('DETAIL_COMMENT',        4);
define('DETAIL_ANNOTATIONS',    5);
define('DETAIL_TARGET_ANNO',    6);
define('DETAIL_GENERIC',        7);
 
/**
 * Represents classes. Every instance of an XP class has a method
 * called getClass() which returns an instance of this class.
 *
 * Warning
 * =======
 * Do not construct this class publicly, instead use either the
 * $o->getClass() syntax or the static method 
 * $class= XPClass::forName('fully.qualified.Name')
 *
 * Examples
 * ========
 * To retrieve the fully qualified name of a class, use this:
 * <code>
 *   $o= new File('...');
 *   echo 'The class name for $o is '.$o->getClass()->getName();
 * </code>
 *
 * Create an instance of a class:
 * <code>
 *   $instance= XPClass::forName('util.Binford')->newInstance();
 * </code>
 *
 * Invoke a method by its name:
 * <code>
 *   try {
 *     $instance->getClass()->getMethod('connect')->invoke($instance);
 *   } catch (TargetInvocationException $e) {
 *     $e->getCause()->printStackTrace();
 *   }
 * </code> 
 *
 * @see   xp://lang.Object#getClass
 * @see   xp://lang.XPClass#forName
 * @test  xp://net.xp_framework.unittest.reflection.XPClassTest
 * @test  xp://net.xp_framework.unittest.reflection.ClassDetailsTest
 * @test  xp://net.xp_framework.unittest.reflection.IsInstanceTest
 * @test  xp://net.xp_framework.unittest.reflection.ClassCastingTest
 */
class XPClass extends Type {
  private $_class;
  private $_reflect= null;

  public static $TYPE_SUPPORTED;

  static function __static() {
    self::$TYPE_SUPPORTED= method_exists('ReflectionParameter', 'getType');

    // Workaround for missing detail information about return types in
    // builtin classes.
    \xp::$meta['php.Exception']= [
      'class' => [4 => null, []],
      0 => [],
      1 => [
        'getMessage'       => [1 => [], 'string', [], null, []],
        'getCode'          => [1 => [], 'int', [], null, []],
        'getFile'          => [1 => [], 'string', [], null, []],
        'getLine'          => [1 => [], 'int', [], null, []],
        'getTrace'         => [1 => [], 'var[]', [], null, []],
        'getPrevious'      => [1 => [], 'lang.Throwable', [], null, []],
        'getTraceAsString' => [1 => [], 'string', [], null, []]
      ]
    ];
  }

  /**
   * Constructor
   *
   * @param   var ref either a class name, a ReflectionClass instance or an object
   */
  public function __construct($ref) {
    if ($ref instanceof \ReflectionClass) {
      $this->_class= $ref->getName();
    } else if (is_object($ref)) {
      $this->_class= get_class($ref);
    } else {
      $this->_class= (string)$ref;
    }
    parent::__construct(self::nameOf($this->_class), null);
  }

  /**
   * Overload member access, retaining BC for public _reflect member.
   *
   * @param  string $name
   * @return var
   */
  public function __get($name) {
    if ('_reflect' === $name) {
      return $this->reflect();
    } else {
      return parent::__get($name);
    }
  }

  /**
   * Returns the reflection object lazily initialized
   *
   * @return php.ReflectionClass
   * @throws  lang.IllegalStateException
   */
  public function reflect() {
    if (null === $this->_reflect) {
      try {
        $this->_reflect= new \ReflectionClass($this->_class);
      } catch (\ReflectionException $e) {
        throw new IllegalStateException($e->getMessage());
      }
    }
    return $this->_reflect;
  }

  /**
   * Returns XP name of a given PHP name.
   *
   * @param  string $class
   * @return string
   */
  public static function nameOf($class) {
    if (isset(\xp::$cn[$class])) {
      return \xp::$cn[$class];
    } else if (strstr($class, '\\')) {
      return strtr($class, '\\', '.');
    } else {
      $name= array_search($class, \xp::$sn, true);
      return false === $name ? $class : $name;
    }
  }

  /**
   * Returns simple name
   *
   * @return  string
   */
  public function getSimpleName() {
    return false === ($p= strrpos(substr($this->name, 0, strcspn($this->name, '<')), '.')) 
      ? $this->name                   // Already unqualified
      : substr($this->name, $p+ 1)    // Full name
    ;
  }
  
  /**
   * Retrieves the package associated with this class
   * 
   * @return  lang.reflect.Package
   */
  public function getPackage() {
    return Package::forName(substr($this->name, 0, strrpos($this->name, '.')));
  }
  
  /**
   * Creates a new instance of the class represented by this Class object.
   * The class is instantiated as if by a new expression with an empty argument list.
   *
   * Example
   * =======
   * <code>
   *   try {
   *     $o= XPClass::forName($name)->newInstance();
   *   } catch (ClassNotFoundException $e) {
   *     // handle it!
   *   }
   * </code>
   *
   * Example (passing arguments)
   * ===========================
   * <code>
   *   try {
   *     $o= XPClass::forName('peer.Socket')->newInstance('localhost', 6100);
   *   } catch (ClassNotFoundException $e) {
   *     // handle it!
   *   }
   * </code>
   *
   * @param   var... args
   * @return  lang.Object 
   * @throws  lang.IllegalAccessException in case this class cannot be instantiated
   */
  public function newInstance(...$args) {
    $reflect= $this->reflect();
    if ($reflect->isInterface()) {
      throw new IllegalAccessException('Cannot instantiate interfaces ('.$this->name.')');
    } else if ($reflect->isTrait()) {
      throw new IllegalAccessException('Cannot instantiate traits ('.$this->name.')');
    } else if ($reflect->isAbstract()) {
      throw new IllegalAccessException('Cannot instantiate abstract classes ('.$this->name.')');
    }
    
    try {
      if ($this->hasConstructor()) {
        return $reflect->newInstanceArgs($args);
      } else {
        return $reflect->newInstance();
      }
    } catch (\ReflectionException $e) {
      throw new IllegalAccessException($e->getMessage());
    }
  }
  
  /**
   * Gets class methods for this class
   *
   * @return  lang.reflect.Method[]
   */
  public function getMethods() {
    $list= [];
    foreach ($this->reflect()->getMethods() as $m) {
      if (0 == strncmp('__', $m->getName(), 2)) continue;
      $list[]= new Method($this->_class, $m);
    }
    return $list;
  }

  /**
   * Gets class methods declared by this class
   *
   * @return  lang.reflect.Method[]
   */
  public function getDeclaredMethods() {
    $list= [];
    $reflect= $this->reflect();
    foreach ($reflect->getMethods() as $m) {
      if (0 == strncmp('__', $m->getName(), 2) || $m->class !== $reflect->name) continue;
      $list[]= new Method($this->_class, $m);
    }
    return $list;
  }

  /**
   * Gets a method by a specified name.
   *
   * @param   string name
   * @return  lang.reflect.Method
   * @see     xp://lang.reflect.Method
   * @throws  lang.ElementNotFoundException
   */
  public function getMethod($name) {
    if ($this->hasMethod($name)) {
      return new Method($this->_class, $this->reflect()->getMethod($name));
    }
    throw new ElementNotFoundException('No such method "'.$name.'" in class '.$this->name);
  }
  
  /**
   * Checks whether this class has a method named "$method" or not.
   *
   * Note
   * ====
   * Since in PHP, methods are case-insensitive, calling hasMethod('toString') 
   * will provide the same result as hasMethod('tostring')
   *
   * @param   string method the method's name
   * @return  bool TRUE if method exists
   */
  public function hasMethod($method) {
    return ((0 === strncmp('__', $method, 2))
      ? false
      : $this->reflect()->hasMethod($method)
    );
  }
  
  /**
   * Retrieve if a constructor exists
   *
   * @return  bool
   */
  public function hasConstructor() {
    return $this->reflect()->hasMethod('__construct');
  }
  
  /**
   * Retrieves this class' constructor.
   *
   * @return  lang.reflect.Constructor
   * @see     xp://lang.reflect.Constructor
   * @throws  lang.ElementNotFoundException
   */
  public function getConstructor() {
    if ($this->hasConstructor()) {
      return new Constructor($this->_class, $this->reflect()->getMethod('__construct')); 
    }
    throw new ElementNotFoundException('No constructor in class '.$this->name);
  }
  
  /**
   * Retrieve a list of all member variables
   *
   * @return  lang.reflect.Field[]
   */
  public function getFields() {
    $f= [];
    foreach ($this->reflect()->getProperties() as $p) {
      if ('__id' === $p->name) continue;
      $f[]= new Field($this->_class, $p);
    }
    return $f;
  }

  /**
   * Retrieve a list of member variables declared in this class
   *
   * @return  lang.reflect.Field[]
   */
  public function getDeclaredFields() {
    $list= [];
    $reflect= $this->reflect();
    if (defined('HHVM_VERSION')) {
      foreach ($reflect->getProperties() as $p) {
        if ('__id' === $p->name || $p->info['class'] !== $reflect->name) continue;
        $list[]= new Field($this->_class, $p);
      }
    } else {
      foreach ($reflect->getProperties() as $p) {
        if ('__id' === $p->name || $p->class !== $reflect->name) continue;
        $list[]= new Field($this->_class, $p);
      }
    }
    return $list;
  }

  /**
   * Retrieve a field by a specified name.
   *
   * @param   string name
   * @return  lang.reflect.Field
   * @throws  lang.ElementNotFoundException
   */
  public function getField($name) {
    if ($this->hasField($name)) {
      return new Field($this->_class, $this->reflect()->getProperty($name));
    }
    throw new ElementNotFoundException('No such field "'.$name.'" in class '.$this->name);
  }
  
  /**
   * Checks whether this class has a field named "$field" or not.
   *
   * @param   string field the fields's name
   * @return  bool TRUE if field exists
   */
  public function hasField($field) {
    return '__id' == $field ? false : $this->reflect()->hasProperty($field);
  }

  /**
   * Retrieve the parent class's class object. Returns NULL if there
   * is no parent class.
   *
   * @return  lang.XPClass class object
   */
  public function getParentclass() {
    return ($parent= $this->reflect()->getParentClass()) ? new self($parent) : null;
  }
  
  /**
   * Checks whether this class has a constant named "$constant" or not
   *
   * @param   string constant
   * @return  bool
   */
  public function hasConstant($constant) {
    return $this->reflect()->hasConstant($constant);
  }
  
  /**
   * Retrieve a constant by a specified name.
   *
   * @param   string constant
   * @return  var
   * @throws  lang.ElementNotFoundException in case constant does not exist
   */
  public function getConstant($constant) {
    if ($this->hasConstant($constant)) {
      return $this->reflect()->getConstant($constant);
    }
    throw new ElementNotFoundException('No such constant "'.$constant.'" in class '.$this->name);
  }

  /**
   * Retrieve class constants
   *
   * @return  [:var]
   */
  public function getConstants() {
    return $this->reflect()->getConstants();
  }

  /**
   * Cast a given object to the class represented by this object
   *
   * @param   var value
   * @return  lang.Generic the given expression
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (null === $value) return null;

    $literal= literal($this->name);
    if ($value instanceof $literal) {
      return $value;
    } else {
      throw new ClassCastException('Cannot cast '.\xp::typeOf($value).' to '.$this->name);
    }
  }
  
  /**
   * Tests whether this class is a subclass of a specified class.
   *
   * @param   var class either a string or an XPClass object
   * @return  bool
   */
  public function isSubclassOf($class) {
    if (!($class instanceof self)) $class= XPClass::forName($class);
    if ($class->name == $this->name) return false;   // Catch bordercase (ZE bug?)
    return $this->reflect()->isSubclassOf($class->reflect());
  }

  /**
   * Tests whether this class is assignable from a given type
   *
   * <code>
   *   // util.Date instanceof lang.Object
   *   XPClass::forName('lang.Object')->isAssignableFrom('util.Date');   // TRUE
   * </code>
   *
   * @param   var type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof self
      ? $t->name === $this->name || $t->reflect()->isSubclassOf($this->reflect())
      : false
    ;
  }

  /**
   * Determines whether the specified object is an instance of this
   * class. This is the equivalent of the is() core functionality.
   *
   * Examples
   * ========
   * ```php
   * $class= XPClass::forName('io.File');
   * 
   * var_dump($class->isInstance(new TempFile()));  // TRUE
   * var_dump($class->isInstance(new File()));      // TRUE
   * var_dump($class->isInstance(new Object()));    // FALSE
   * ```
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj) {
    return is($this->name, $obj);
  }

  /**
   * Determines if this XPClass object represents an interface type.
   *
   * @return  bool
   */
  public function isInterface() {
    return $this->reflect()->isInterface();
  }

  /**
   * Determines if this XPClass object represents a trait type.
   *
   * @return  bool
   */
  public function isTrait() {
    return $this->reflect()->isTrait();
  }

  /**
   * Determines if this XPClass object represents an enum type.
   *
   * @return  bool
   */
  public function isEnum() {
    return
      (class_exists('lang\Enum', false) && $this->reflect()->isSubclassOf('lang\Enum')) ||
      (class_exists('HH\BuiltinEnum', false) && $this->reflect()->isSubclassOf('HH\BuiltinEnum'))
    ;
  }

  /**
   * Retrieve traits this class uses
   *
   * @return  lang.XPClass[]
   */
  public function getTraits() {
    $r= [];
    foreach ($this->reflect()->getTraits() as $used) {
      if (0 !== strncmp($used->getName(), '__', 2)) {
        $r[]= new self($used);
      }
    }
    return $r;
  }

  /**
   * Retrieve interfaces this class implements
   *
   * @return  lang.XPClass[]
   */
  public function getInterfaces() {
    $r= [];
    foreach ($this->reflect()->getInterfaces() as $iface) {
      $r[]= new self($iface);
    }
    return $r;
  }

  /**
   * Retrieve interfaces this class implements in its declaration
   *
   * @return  lang.XPClass[]
   */
  public function getDeclaredInterfaces() {
    $reflect= $this->reflect();
    $is= $reflect->getInterfaces();
    if ($parent= $reflect->getParentclass()) {
      $ip= $parent->getInterfaces();
    } else {
      $ip= [];
    }
    $filter= [];
    foreach ($is as $iname => $i) {

      // Parent class implements this interface
      if (isset($ip[$iname])) {
        $filter[$iname]= true;
        continue;
      }

      // Interface is implemented because it's the parent of another interface
      foreach ($i->getInterfaces() as $pname => $p) {
        if (isset($is[$pname])) $filter[$pname]= true;
      }
    }
    
    $r= [];
    foreach ($is as $iname => $i) {
      if (!isset($filter[$iname])) $r[]= new self($i);
    }
    return $r;
  }
  

  /**
   * Retrieves the api doc comment for this class. Returns NULL if
   * no documentation is present.
   *
   * @return  string
   */
  public function getComment() {
    if (!($details= self::detailsForClass($this->name))) return null;
    return $details['class'][DETAIL_COMMENT];
  }

  /**
   * Retrieves this class' modifiers
   *
   * @see     xp://lang.reflect.Modifiers
   * @return  int
   */
  public function getModifiers() {
    $r= MODIFIER_PUBLIC;

    // Map PHP reflection modifiers to generic form
    $m= $this->reflect()->getModifiers();
    $m & \ReflectionClass::IS_EXPLICIT_ABSTRACT && $r |= MODIFIER_ABSTRACT;
    $m & \ReflectionClass::IS_IMPLICIT_ABSTRACT && $r |= MODIFIER_ABSTRACT;
    $m & \ReflectionClass::IS_FINAL && $r |= MODIFIER_FINAL;
    
    return $r;
  }

  /**
   * Check whether an annotation exists
   *
   * @param   string name
   * @param   string key default NULL
   * @return  bool
   */
  public function hasAnnotation($name, $key= null) {
    $details= self::detailsForClass($this->name);
    if ($details && ($annotations= $details['class'][DETAIL_ANNOTATIONS])) {
      return $key ? array_key_exists($key, @$annotations[$name]) : array_key_exists($name, $annotations);
    } else if (defined('HHVM_VERSION')) {
      $attr= $this->reflect()->getAttributes();
      return $key ? isset($attr[$name]) && array_key_exists($key, @$attr[$name][0]) : isset($attr[$name]); 
    }
    return false;
  }

  /**
   * Retrieve annotation by name
   *
   * @param   string name
   * @param   string key default NULL
   * @return  var
   * @throws  lang.ElementNotFoundException
   */
  public function getAnnotation($name, $key= null) {
    $details= self::detailsForClass($this->name);
    if ($details && ($annotations= $details['class'][DETAIL_ANNOTATIONS])) {
      if ($key) {
        if (array_key_exists($key, @$annotations[$name])) return $annotations[$name][$key];  
      } else {
        if (array_key_exists($name, $annotations)) return $annotations[$name];  
      }  
    } else if (defined('HHVM_VERSION')) {
      $attr= $this->reflect()->getAttributes();
      if ($key) {
        if (isset($attr[$name]) && array_key_exists($key, @$attr[$name][0])) return $attr[$name][0][$key];
      } else {
        if (isset($attr[$name])) return empty($attr[$name]) ? null : $attr[$name][0];
      }  
    }
    throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
  }

  /**
   * Retrieve whether a method has annotations
   *
   * @return  bool
   */
  public function hasAnnotations() {
    $details= self::detailsForClass($this->name);
    if ($details && $details['class'][DETAIL_ANNOTATIONS]) {
      return true;
    } else if (defined('HHVM_VERSION')) {
      return sizeof($this->reflect()->getAttributes()) > 0; 
    }
    return false;
  }

  /**
   * Retrieve all of a method's annotations
   *
   * @return  array annotations
   */
  public function getAnnotations() {
    $details= self::detailsForClass($this->name);
    if ($details && $details['class'][DETAIL_ANNOTATIONS]) {
      return $details['class'][DETAIL_ANNOTATIONS];
    } else if (defined('HHVM_VERSION')) {
      $return= [];
      foreach (array_reverse($this->reflect()->getAttributes()) as $name => $attr) {
        $return[$name]= empty($attr) ? null : $attr[0];
      }
      return $return;
    }
    return [];
  }
  
  /**
   * Retrieve the class loader a class was loaded with.
   *
   * @return  lang.IClassLoader
   */
  public function getClassLoader() {
    return self::_classLoaderFor($this->name);
  }
  
  /**
   * Fetch a class' classloader by its name
   *
   * @param   string name fqcn of class
   * @return  lang.IClassLoader
   */
  protected static function _classLoaderFor($name) {
    if (isset(\xp::$cl[$name])) {
      sscanf(\xp::$cl[$name], '%[^:]://%[^$]', $cl, $argument);
      $instanceFor= [literal($cl), 'instanceFor'];
      return $instanceFor($argument);
    }
    return null;    // Internal class, e.g.
  }
  
  /**
   * Retrieve details for a specified class. Note: Results from this 
   * method are cached!
   *
   * @param   string class fully qualified class name
   * @return  array or NULL to indicate no details are available
   */
  public static function detailsForClass($class) {
    static $parser= null;

    if (!$class) {                                              // Border case
      return null;
    } else if (isset(\xp::$meta[$class])) {                     // Cached
      return \xp::$meta[$class];
    } else if (isset(\xp::$registry[$l= 'details.'.$class])) {  // BC: Cached in registry
      return \xp::$registry[$l];
    }

    // Retrieve class' sourcecode
    $cl= self::_classLoaderFor($class);
    if (!$cl || !($bytes= $cl->loadClassBytes($class))) return null;

    // Return details for specified class
    if (!$parser) {
      $parser= new \lang\reflect\ClassParser();
    }
    return \xp::$meta[$class]= $parser->parseDetails($bytes, $class);
  }

  /**
   * Retrieve details for a specified class and method. Note: Results 
   * from this method are cached!
   *
   * @param   php.ReflectionClass $class
   * @param   string $method
   * @return  array or NULL if not available
   */
  public static function detailsForMethod($class, $method) {
    $details= self::detailsForClass(self::nameOf($class->name));
    if (isset($details[1][$method])) return $details[1][$method];
    foreach ($class->getTraitNames() as $trait) {
      $details= self::detailsForClass(self::nameOf($trait));
      if (isset($details[1][$method])) return $details[1][$method];
    }
    return null;
  }

  /**
   * Retrieve details for a specified class and field. Note: Results 
   * from this method are cached!
   *
   * @param   php.ReflectionClass $class
   * @param   string method
   * @return  array or NULL if not available
   */
  public static function detailsForField($class, $field) {
    $details= self::detailsForClass(self::nameOf($class->name));
    if (isset($details[0][$field])) return $details[0][$field];
    foreach ($class->getTraitNames() as $trait) {
      $details= self::detailsForClass(self::nameOf($trait));
      if (isset($details[0][$field])) return $details[0][$field];
    }
    return null;
  }

  /**
   * Reflectively creates a new type
   *
   * @param   lang.Type[] arguments
   * @return  lang.XPClass
   * @throws  lang.IllegalStateException if this class is not a generic definition
   * @throws  lang.IllegalArgumentException if number of arguments does not match components
   */
  public function newGenericType($arguments) {
    static $creator= null;

    if (!$creator) {
      $creator= new GenericTypes();
    }
    return $creator->newType($this, $arguments);
  }

  /**
   * Returns generic type components
   *
   * @return  string[]
   * @throws  lang.IllegalStateException if this class is not a generic definition
   */
  public function genericComponents() {
    if (!$this->isGenericDefinition()) {
      throw new IllegalStateException('Class '.$this->name.' is not a generic definition');
    }
    $components= [];
    foreach (explode(',', $this->getAnnotation('generic', 'self')) as $name) {
      $components[]= ltrim($name);
    }
    return $components;
  }

  /**
   * Returns whether this class is a generic definition
   *
   * @return  bool
   */
  public function isGenericDefinition() {
    return $this->hasAnnotation('generic', 'self');
  }

  /**
   * Returns generic type definition
   *
   * @return  lang.XPClass
   * @throws  lang.IllegalStateException if this class is not a generic
   */
  public function genericDefinition() {
    if (!($details= self::detailsForClass($this->name))) return null;
    if (!isset($details['class'][DETAIL_GENERIC])) {
      throw new IllegalStateException('Class '.$this->name.' is not generic');
    }
    return XPClass::forName($details['class'][DETAIL_GENERIC][0]);
  }

  /**
   * Returns generic type arguments
   *
   * @return  lang.Type[]
   * @throws  lang.IllegalStateException if this class is not a generic
   */
  public function genericArguments() {
    if (!($details= self::detailsForClass($this->name))) return null;
    if (!isset($details['class'][DETAIL_GENERIC])) {
      throw new IllegalStateException('Class '.$this->name.' is not generic');
    }
    if (!isset($details['class'][DETAIL_GENERIC][1])) {
      $details['class'][DETAIL_GENERIC][1]= array_map(
        ['lang\Type', 'forName'], 
        $details['class'][DETAIL_GENERIC][2]
      );
      unset($details['class'][DETAIL_GENERIC][2]);
    }
    return $details['class'][DETAIL_GENERIC][1];
  }
      
  /**
   * Returns whether this class is generic
   *
   * @return  bool
   */
  public function isGeneric() {
    if (!($details= self::detailsForClass($this->name))) return false;
    return isset($details['class'][DETAIL_GENERIC]);
  }
  
  /**
   * Returns the XPClass object associated with the class with the given 
   * string name. Uses the default classloader if none is specified.
   *
   * @param   string name - e.g. "Exception", "io.File" or "lang.XPClass"
   * @param   lang.IClassLoader classloader default NULL
   * @return  lang.XPClass class object
   * @throws  lang.ClassNotFoundException when there is no such class
   */
  public static function forName($name, IClassLoader $classloader= null) {
    $qualified= strtr($name, '.', '\\');
    if (class_exists($qualified, false) || interface_exists($qualified, false) || trait_exists($qualified, false)) {
      return new self($qualified);
    } else if (null === $classloader) {
      return ClassLoader::getDefault()->loadClass(strtr($name, '\\', '.'));
    } else {
      return $classloader->loadClass(strtr($name, '\\', '.'));
    }
  }

  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    return literal($this->name);
  }
  
  /**
   * Returns an array containing class objects representing all the 
   * public classes
   *
   * @return  lang.XPClass[] class objects
   */
  public static function getClasses() {
    foreach (\xp::$cl as $class => $loader) {
      $ret[]= new self(literal($class));
    }
    return $ret;
  }
}
