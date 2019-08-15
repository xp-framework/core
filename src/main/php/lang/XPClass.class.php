<?php namespace lang;

use lang\ElementNotFoundException;
use lang\reflect\{Method, Field, Constructor, Package};

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
 * Invoke a method by its name:
 * ```php
 * try {
 *   typeof($instance)->getMethod('connect')->invoke($instance);
 * } catch (TargetInvocationException $e) {
 *   $e->getCause()->printStackTrace();
 * }
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
  public function getMethod($name): Method {
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
  public function hasMethod($method): bool {
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
  public function hasConstructor(): bool {
    return $this->reflect()->hasMethod('__construct');
  }
  
  /**
   * Retrieves this class' constructor.
   *
   * @return  lang.reflect.Constructor
   * @see     xp://lang.reflect.Constructor
   * @throws  lang.ElementNotFoundException
   */
  public function getConstructor(): Constructor {
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
    $reflect= $this->reflect()->name;
    foreach ($reflect->getProperties() as $p) {
      if ('__id' === $p->name || $p->class !== $reflect) continue;
      $list[]= new Field($this->_class, $p);
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
  public function getField($name): Field {
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
  public function hasField($field): bool {
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
  public function hasConstant($constant): bool {
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
   * @return  var the given expression
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (null === $value) return null;

    $literal= literal($this->name);
    if ($value instanceof $literal) return $value;

    // Check for a class with a single-arg constructor with matching type
    $reflect= $this->reflect();
    if (
      $reflect->isInstantiable() &&
      ($constructor= $this->getConstructor()) &&
      $constructor->numParameters() > 0 &&
      $constructor->getParameter(0)->getType()->isInstance($value)
    ) {
      try {
        return $reflect->newInstance($value);
      } catch (\ReflectionException $e) {
        throw new ClassCastException('Cannot cast '.typeof($value)->getName().' to '.$this->name, $e);
      }
    }

    throw new ClassCastException('Cannot cast '.typeof($value)->getName().' to '.$this->name);
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
    return class_exists(Enum::class, false) && $this->reflect()->isSubclassOf(Enum::class);
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

  /**
   * Check whether an annotation exists
   *
   * @param   string name
   * @param   string key default NULL
   * @return  bool
   */
  public function hasAnnotation($name, $key= null): bool {
    $details= self::detailsForClass($this->name);
    
    return $details && ($key 
      ? array_key_exists($key, $details['class'][DETAIL_ANNOTATIONS][$name] ?? []) 
      : array_key_exists($name, $details['class'][DETAIL_ANNOTATIONS] ?? [])
    );
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
    if (!$details || !($key 
      ? array_key_exists($key, $details['class'][DETAIL_ANNOTATIONS][$name] ?? []) 
      : array_key_exists($name, $details['class'][DETAIL_ANNOTATIONS] ?? [])
    )) {
      throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
    }

    return ($key 
      ? $details['class'][DETAIL_ANNOTATIONS][$name][$key] 
      : $details['class'][DETAIL_ANNOTATIONS][$name]
    );
  }

  /** Retrieve whether a method has annotations */
  public function hasAnnotations(): bool {
    $details= self::detailsForClass($this->name);
    return $details ? !empty($details['class'][DETAIL_ANNOTATIONS]) : false;
  }

  /**
   * Retrieve all of a method's annotations
   *
   * @return  array annotations
   */
  public function getAnnotations() {
    $details= self::detailsForClass($this->name);
    return $details ? $details['class'][DETAIL_ANNOTATIONS] : [];
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

    $parser ?? $parser= new \lang\reflect\ClassParser();
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
  public function isGenericDefinition(): bool {
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

    if (class_exists($resolved, false) || interface_exists($resolved, false) || trait_exists($resolved, false)) {
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
