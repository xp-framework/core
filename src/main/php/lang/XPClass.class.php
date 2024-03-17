<?php namespace lang;

use lang\ElementNotFoundException;
use lang\reflect\{Method, Field, Constructor, Package, ClassParser};
 
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
 * ```php
 * $file= new File('...');
 * echo 'The class name for $file is '.typeof($file)->getName();
 * ```
 *
 * Create an instance of a class:
 * ```php
 * $instance= XPClass::forName('util.Binford')->newInstance();
 * ```
 *
 * @see   xp://lang.XPClass#forName
 * @test  xp://net.xp_framework.unittest.reflection.XPClassTest
 * @test  xp://net.xp_framework.unittest.reflection.ClassDetailsTest
 * @test  xp://net.xp_framework.unittest.reflection.IsInstanceTest
 * @test  xp://net.xp_framework.unittest.reflection.ClassCastingTest
 */
class XPClass extends Type {
  private $_class;
  private $_reflect= null;

  static function __static() {

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
      $this->_reflect= $ref;
    } else if ($ref instanceof \__PHP_Incomplete_Class) {
      throw new ClassCastException('Cannot use incomplete classes in reflection');
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

  /** Returns simple name */
  public function getSimpleName(): string {
    return false === ($p= strrpos(substr($this->name, 0, strcspn($this->name, '<')), '.')) 
      ? $this->name                   // Already unqualified
      : substr($this->name, $p+ 1)    // Full name
    ;
  }
  
  /** Retrieves the package associated with this class */
  public function getPackage(): Package {
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
   * @param   var... $args
   * @return  object
   * @throws  lang.IllegalAccessException in case this class cannot be instantiated
   */
  public function newInstance(... $args) {
    $reflect= $this->reflect();
    if ($reflect->isInterface()) {
      throw new IllegalAccessException('Cannot instantiate interfaces ('.$this->name.')');
    } else if ($reflect->isTrait()) {
      throw new IllegalAccessException('Cannot instantiate traits ('.$this->name.')');
    } else if ($reflect->isAbstract()) {
      throw new IllegalAccessException('Cannot instantiate abstract classes ('.$this->name.')');
    }

    try {
      return $reflect->newInstance(...$args);
    } catch (\ReflectionException $e) {
      throw new IllegalAccessException($e->getMessage(), $e);
    }
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
   * Cast a given object to the class represented by this object
   *
   * @param   var value
   * @return  var the given expression
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (null === $value) return null;

    $literal= literal($this->name);
    if ($value instanceof $literal) {
      return $value;
    } else {
      throw new ClassCastException('Cannot cast '.typeof($value)->getName().' to '.$this->name);
    }
  }
  
  /**
   * Tests whether this class is a subclass of a specified class.
   *
   * @param   string|self $class
   * @return  bool
   */
  public function isSubclassOf($class): bool {
    if (!($class instanceof self)) $class= XPClass::forName($class);
    return $class->name === $this->name ? false : $this->reflect()->isSubclassOf($class->reflect());
  }

  /**
   * Tests whether this class is assignable from a given type
   *
   * <code>
   *   // util.Date instanceof lang.Value
   *   XPClass::forName('lang.Value')->isAssignableFrom('util.Date');   // TRUE
   * </code>
   *
   * @param   string|lang.Type $type
   * @return  bool
   */
  public function isAssignableFrom($type): bool {
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
   * var_dump($class->isInstance(new Date()));      // FALSE
   * ```
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj): bool {
    return is($this->name, $obj);
  }

  /**
   * Determines if this XPClass object represents an interface type.
   *
   * @return  bool
   */
  public function isInterface(): bool {
    return $this->reflect()->isInterface();
  }

  /**
   * Determines if this XPClass object represents a trait type.
   *
   * @return  bool
   */
  public function isTrait(): bool {
    return $this->reflect()->isTrait();
  }

  /**
   * Determines if this XPClass object represents an enum type.
   *
   * @return  bool
   */
  public function isEnum(): bool {
    $r= $this->reflect();
    return $r->isSubclassOf(Enum::class) || $r->isSubclassOf(\UnitEnum::class);
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
  public function getModifiers(): int {
    $r= MODIFIER_PUBLIC;

    // Map PHP reflection modifiers to generic form
    $m= $this->reflect()->getModifiers();
    $m & \ReflectionClass::IS_EXPLICIT_ABSTRACT && $r |= MODIFIER_ABSTRACT;
    $m & \ReflectionClass::IS_IMPLICIT_ABSTRACT && $r |= MODIFIER_ABSTRACT;
    $m & \ReflectionClass::IS_FINAL && $r |= MODIFIER_FINAL;
    
    return $r;
  }

  /** Retrieve the class loader a class was loaded with */
  public function getClassLoader(): IClassLoader {
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

    if (isset(\xp::$meta[$class])) return \xp::$meta[$class];

    // Retrieve class' sourcecode
    $cl= self::_classLoaderFor($class);
    if (!$cl || !($bytes= $cl->loadClassBytes($class))) return null;

    $parser ?? $parser= new ClassParser();
    return \xp::$meta[$class]= $parser->parseDetails($bytes);
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

    $details= XPClass::detailsForClass($this->name);
    $annotations= $details ? $details['class'][DETAIL_ANNOTATIONS] : [];
    $components= [];
    foreach (explode(',', $annotations['generic']['self']) as $name) {
      $components[]= ltrim($name);
    }
    return $components;
  }

  /**
   * Returns whether this class is a generic definition
   *
   * @return  bool
   */
  public function isGenericDefinition(): bool {
    $details= XPClass::detailsForClass($this->name);
    $annotations= $details ? $details['class'][DETAIL_ANNOTATIONS] : [];
    return isset($annotations['generic']['self']);
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
        [Type::class, 'forName'], 
        $details['class'][DETAIL_GENERIC][2]
      );
      unset($details['class'][DETAIL_GENERIC][2]);
    }
    return $details['class'][DETAIL_GENERIC][1];
  }
      
  /** Returns whether this class is generic */
  public function isGeneric(): bool {
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
  public static function forName($name, IClassLoader $classloader= null): self {
    $p= strpos($name, '\\');
    if (false === $p) {     // No backslashes, using dotted form
      $resolved= strtr($name, '.', '\\');
    } else {                // Name literal
      $resolved= 0 === $p ? substr($name, 1) : $name;
      $name= strtr($resolved, '\\', '.');
    }

    if (class_exists($resolved, false) || interface_exists($resolved, false) || trait_exists($resolved, false) || enum_exists($resolved, false)) {
      return new self($resolved);
    } else if (null === $classloader) {
      return ClassLoader::getDefault()->loadClass($name);
    } else {
      return $classloader->loadClass($name);
    }
  }

  /** Returns type literal */
  public function literal(): string {
    return literal($this->name);
  }
  
  /** Returns all loaded classes */
  public static function getClasses(): \Traversable {
    foreach (\xp::$cl as $class => $loader) {
      yield new self(literal($class));
    }
  }
}
