<?php namespace lang;

use lang\reflect\Method;
use lang\reflect\Field;
use lang\reflect\Constructor;
use lang\reflect\Package;

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
  protected $_class= null;
  public $_reflect= null;

  static function __static() { }

  /**
   * Constructor
   *
   * @param   var ref either a class name, a ReflectionClass instance or an object
   * @throws  lang.IllegalStateException
   */
  public function __construct($ref) {
    if ($ref instanceof \ReflectionClass) {
      $this->_reflect= $ref;
      $this->_class= $ref->getName();
    } else if (is_object($ref)) {
      $this->_reflect= new \ReflectionClass($ref);
      $this->_class= get_class($ref);
    } else {
      try {
        $this->_reflect= new \ReflectionClass((string)$ref);
      } catch (\ReflectionException $e) {
        throw new IllegalStateException($e->getMessage());
      }
      $this->_class= $ref;
    }
    parent::__construct(\xp::nameOf($this->_class), null);
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
   * @param   var* args
   * @return  lang.Object 
   * @throws  lang.IllegalAccessException in case this class cannot be instantiated
   */
  public function newInstance($value= null) {
    if ($this->_reflect->isInterface()) {
      throw new IllegalAccessException('Cannot instantiate interfaces ('.$this->name.')');
    } else if ($this->_reflect->isAbstract()) {
      throw new IllegalAccessException('Cannot instantiate abstract classes ('.$this->name.')');
    }
    
    try {
      if (!$this->hasConstructor()) return $this->_reflect->newInstance();
      $args= func_get_args();
      return $this->_reflect->newInstanceArgs($args);
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
    foreach ($this->_reflect->getMethods() as $m) {
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
    foreach ($this->_reflect->getMethods() as $m) {
      if (0 == strncmp('__', $m->getName(), 2) || $m->class !== $this->_reflect->name) continue;
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
      return new Method($this->_class, $this->_reflect->getMethod($name));
    }
    raise('lang.ElementNotFoundException', 'No such method "'.$name.'" in class '.$this->name);
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
      : $this->_reflect->hasMethod($method)
    );
  }
  
  /**
   * Retrieve if a constructor exists
   *
   * @return  bool
   */
  public function hasConstructor() {
    return $this->_reflect->hasMethod('__construct');
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
      return new Constructor($this->_class, $this->_reflect->getMethod('__construct')); 
    }
    raise('lang.ElementNotFoundException', 'No constructor in class '.$this->name);
  }
  
  /**
   * Retrieve a list of all member variables
   *
   * @return  lang.reflect.Field[]
   */
  public function getFields() {
    $f= [];
    foreach ($this->_reflect->getProperties() as $p) {
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
    if (defined('HHVM_VERSION')) {
      foreach ($this->_reflect->getProperties() as $p) {
        if ('__id' === $p->name || $p->info['class'] !== $this->_reflect->name) continue;
        $list[]= new Field($this->_class, $p);
      }
    } else {
      foreach ($this->_reflect->getProperties() as $p) {
        if ('__id' === $p->name || $p->class !== $this->_reflect->name) continue;
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
      return new Field($this->_class, $this->_reflect->getProperty($name));
    }
    raise('lang.ElementNotFoundException', 'No such field "'.$name.'" in class '.$this->name);
  }
  
  /**
   * Checks whether this class has a field named "$field" or not.
   *
   * @param   string field the fields's name
   * @return  bool TRUE if field exists
   */
  public function hasField($field) {
    return '__id' == $field ? false : $this->_reflect->hasProperty($field);
  }

  /**
   * Retrieve the parent class's class object. Returns NULL if there
   * is no parent class.
   *
   * @return  lang.XPClass class object
   */
  public function getParentclass() {
    return ($parent= $this->_reflect->getParentClass()) ? new self($parent) : null;
  }
  
  /**
   * Checks whether this class has a constant named "$constant" or not
   *
   * @param   string constant
   * @return  bool
   */
  public function hasConstant($constant) {
    return $this->_reflect->hasConstant($constant);
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
      return $this->_reflect->getConstant($constant);
    }
    
    raise('lang.ElementNotFoundException', 'No such constant "'.$constant.'" in class '.$this->name);
  }

  /**
   * Retrieve class constants
   *
   * @return  [:var]
   */
  public function getConstants() {
    return $this->_reflect->getConstants();
  }

  /**
   * Cast a given object to the class represented by this object
   *
   * @param   var value
   * @return  lang.Generic the given expression
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (null === $value) {
      return \xp::null();
    } else if (is($this->name, $value)) {
      return $value;
    }
    raise('lang.ClassCastException', 'Cannot cast '.\xp::typeOf($value).' to '.$this->name);
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
    return $this->_reflect->isSubclassOf($class->_reflect);
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
      ? $t->name === $this->name || $t->_reflect->isSubclassOf($this->_reflect)
      : false
    ;
  }

  /**
   * Determines whether the specified object is an instance of this
   * class. This is the equivalent of the is() core functionality.
   *
   * Examples
   * ========
   * <code>
   *   uses('io.File', 'io.TempFile');
   *   $class= XPClass::forName('io.File');
   * 
   *   var_dump($class->isInstance(new TempFile()));  // TRUE
   *   var_dump($class->isInstance(new File()));      // TRUE
   *   var_dump($class->isInstance(new Object()));    // FALSE
   * </code>
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
    return $this->_reflect->isInterface();
  }

  /**
   * Determines if this XPClass object represents an interface type.
   *
   * @return  bool
   */
  public function isEnum() {
    return class_exists('lang\Enum', false) && $this->_reflect->isSubclassOf('lang\Enum');
  }
  
  /**
   * Retrieve interfaces this class implements
   *
   * @return  lang.XPClass[]
   */
  public function getInterfaces() {
    $r= [];
    foreach ($this->_reflect->getInterfaces() as $iface) {
      $r[]= new self($iface->getName());
    }
    return $r;
  }

  /**
   * Retrieve interfaces this class implements in its declaration
   *
   * @return  lang.XPClass[]
   */
  public function getDeclaredInterfaces() {
    $is= $this->_reflect->getInterfaces();
    if ($parent= $this->_reflect->getParentclass()) {
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
    $m= $this->_reflect->getModifiers();
    $m & \ReflectionClass::IS_EXPLICIT_ABSTRACT && $r |= MODIFIER_ABSTRACT;
    $m & \ReflectionClass::IS_IMPLICIT_ABSTRACT && $r |= MODIFIER_ABSTRACT;
    $m & \ReflectionClass::IS_FINAL && $r |= MODIFIER_FINAL;
    
    return $r;
  }

  /**
   * Convert a HACK attribute to an XP annotation
   *
   * @param  var[] $value
   * @return var
   */
  private function attribute($value) { return empty($value) ? null : $value[0]; }

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
      $attr= $this->_reflect->getAttributes();
      return $key ? isset($attr[$name][$key]) : isset($attr[$name]);
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
      $attr= $this->_reflect->getAttributes();
      if ($key) {
        if (isset($attr[$name][$key])) return $this->attribute($attr[$name][$key]);
      } else {
        if (isset($attr[$name])) return $this->attribute($attr[$name]);
      }
    }

    raise('lang.ElementNotFoundException', 'Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
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
      return sizeof($this->_reflect->getAttributes()) > 0;
    } else {
      return false;
    }
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
      return array_map([$this, 'attribute'], array_reverse($this->_reflect->getAttributes()));
    } else {
      return [];
    }
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
      return call_user_func([literal($cl), 'instanceFor'], $argument);
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
   * @param   string class unqualified class name
   * @param   string method
   * @return  array or NULL if not available
   */
  public static function detailsForMethod($class, $method) {
    $details= self::detailsForClass(\xp::nameOf($class));
    return $details ? (isset($details[1][$method]) ? $details[1][$method] : null) : null;
  }

  /**
   * Retrieve details for a specified class and field. Note: Results 
   * from this method are cached!
   *
   * @param   string class unqualified class name
   * @param   string method
   * @return  array or NULL if not available
   */
  public static function detailsForField($class, $field) {
    $details= self::detailsForClass(\xp::nameOf($class));
    return $details ? (isset($details[0][$field]) ? $details[0][$field] : null) : null;
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
   * @param   string name - e.g. "io.File", "rdbms.mysql.MySQL"
   * @param   lang.IClassLoader classloader default NULL
   * @return  lang.XPClass class object
   * @throws  lang.ClassNotFoundException when there is no such class
   */
  public static function forName($name, IClassLoader $classloader= null) {
    if (null === $classloader) {
      $classloader= ClassLoader::getDefault();
    }

    return $classloader->loadClass(strtr($name, '\\', '.'));
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
