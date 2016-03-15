<?php namespace unittest\mock;

use lang\ClassLoader;
use lang\reflect\Modifiers;

/**
 * Provides functionallity for creating dynamic proxy
 * classes and instances.
 */
class MockProxyBuilder extends \lang\Object {   
  const PREFIX = 'MockProxy·';

  private
    $classLoader       = null,
    $overwriteExisting = false,
    $added             = [];

  private static 
    $num               = 0,
    $cache             = [];
  
  /**
   * Constructor
   * 
   * @param   lang.ClassLoader classLoader
   */
  public function  __construct($classLoader= null) {
    if (null === $classLoader) {
      $this->classLoader= ClassLoader::getDefault();
    } else {
      $this->classLoader= $classLoader;
    }
  }
  
  /**
   * Sets whether to overwrite existing implementations of concrete methods.
   * 
   * @param boolean value
   */
  public function setOverwriteExisting($value){
    $this->overwriteExisting= $value;
  }
     
  /**
   * Returns the XPClass object for a proxy class given a class loader 
   * and an array of interfaces.  The proxy class will be defined by the 
   * specified class loader and will implement all of the supplied 
   * interfaces (also loaded by the classloader).
   *
   * @param   lang.IClassLoader classloader
   * @param   lang.XPClass[] interfaces names of the interfaces to implement
   * @return  lang.XPClass baseClass
   * @throws  lang.IllegalArgumentException
   */
  public function createProxyClass(\lang\IClassLoader $classloader, array $interfaces, $baseClass= null) {
    $this->added= [];

    if (!$baseClass) {
      $baseClass= \lang\XPClass::forName('lang.Object');
    }

    // Check if class is already in cache
    $key= $this->buildCacheId($baseClass, $interfaces);
    if (null !== ($cached=$this->tryGetFromCache($key))) {
      return $cached;
    }

    // Write class definition:
    // Class <name> extends <baseClass> implements IProxy, <interfaces> {
    $bytes= $this->generateHead($baseClass, $interfaces);

    // Add instance variables and constructor
    $bytes.= $this->generatePreamble();

    // Generate code for (abstract) class methods (if any)
    $bytes.= $this->generateBaseClassMethods($baseClass);

    // Generate code for interface methods
    for ($j= 0; $j < sizeof($interfaces); $j++) {
      $bytes.= $this->generateInterfaceMethods($interfaces[$j]);
    }

    // Done.
    $bytes.= ' }';

    // Create the actual class
    $class= $this->createClass($bytes);

    // Update cache+counter and return XPClass object
    self::$cache[$key]= $class;
    self::$num++;
    
    return $class;
  }

  /**
   * Generates the class header.
   * "class <name> extends <baseClass> implements IProxy, <interfaces> {"
   *
   * @param lang.XPClass baseClass
   * @param lang.XPClass[] interfaces
   * @return string 
   */
  private function generateHead($baseClass, $interfaces) {
    // Create proxy class' name, using a unique identifier and a prefix
    $name= $this->getProxyName();
    \xp::$cn[$name]= $name;
    $bytes= 'class '.$name.' extends '.$baseClass->literal().' implements \unittest\mock\IMockProxy, ';

    for ($j= 0; $j < sizeof($interfaces); $j++) {
      $bytes.= $interfaces[$j]->literal().', ';
    }
    $bytes= substr($bytes, 0, -2)." {\n";

    return $bytes;
  }

  /**
   * Generates the name for the current proxy from a prefix and a counter.
   *
   * @return  string
   */
  public function getProxyName() {
    return self::PREFIX.(self::$num);
  }

  /**
   * Check if the class is already cached and returns it, otherwise returns null.
   *
   * @param   string key
   * @return  lang.XPClass
   */
  private function tryGetFromCache($key) {
    if (isset(self::$cache[$key])) {
      return self::$cache[$key];
    }
    
    return null;
  }
  
  /**
   * Calculate cache key (composed of the names of all interfaces)
   *
   * @param   lang.XPClass baseClass
   * @param   lang.XPClass[] interfaces
   * @return  string
   */
  private function buildCacheId($baseClass, $interfaces) {
    $key= $this->classLoader->hashCode().':'.$baseClass->getName().';';
    $key.= implode(';', array_map(function($i) { return $i->getName(); }, $interfaces));
    $key.= $this->overwriteExisting?'override':'';

    return $key;
  }

  /**
   * Returns the name of the handler variable
   *
   * @return  string
   */
  private function getHandlerName() {
    return '_h';
  }

  /**
   * Generates code for the class preamble containing initializations of
   * variables and the constructor.
   *
   * @return  string
   */
  private function generatePreamble() {
    $handlerName= $this->getHandlerName();
    
    $preamble= 'private $'.$handlerName.'= null;'."\n\n";
    $preamble.= 'public function __construct($handler) {'."\n".
               '  $this->'.$handlerName.'= $handler;'."\n".
               "}\n";

    return $preamble;
  }

  /**
   * Generates code for implementing all interface methods.
   * 
   * @param   lang.XPClass if
   * @return  string
   */
  private function generateInterfaceMethods($if) {
    $bytes= '';

    // Verify that the Class object actually represents an interface
    if (!$if->isInterface()) {
      throw new \lang\IllegalArgumentException($if->getName().' is not an interface');
    }

    // Implement all the interface's methods
    // Check for already declared methods, do not redeclare them
    foreach ($if->getMethods() as $m) {
      if (isset($this->added[$m->getName()])) continue;
      $this->added[$m->getName()]= true;
      $bytes.= $this->generateMethod($m);
    }
    return $bytes;
  }

  /**
   * Generates code for (re)implementation of the (abstract) class methods
   * of the base class.
   *
   * @param lang.XPClass baseClass
   */
  private function generateBaseClassMethods($baseClass) {
    $bytes= '';

    $bytes.= 'static function __static() {}';

    $reservedMethods= \lang\XPClass::forName('lang.Generic')->getMethods();
    $reservedMethodNames= array_map(function($i) { return $i->getName(); }, $reservedMethods);
    
    foreach ($baseClass->getMethods() as $m) {

      // do not overwrite reserved methods, omit static methods
      if (in_array($m->getName(), $reservedMethodNames) || Modifiers::isStatic($m->getModifiers())) continue;
        
      // Check for already declared methods, do not redeclare them
      // implement abstract methods
      if ($this->overwriteExisting || ($m->getModifiers() & 2) == 2) {
        if (isset($this->added[$m->getName()])) continue;
        $this->added[$m->getName()]= true;
        $bytes.= $this->generateMethod($m);
      }
    }
    return $bytes;
  }

  
  /**
   * Generates code for a method.
   * 
   * @param lang.reflect.Method method
   * @return string
   */
  private function generateMethod($method) {       
    $bytes= '';

    // Build signature and argument list
    if ($method->hasAnnotation('overloaded')) {
      $signatures= $method->getAnnotation('overloaded', 'signatures');
      $methodax= 0;
      $cases= [];
      foreach ($signatures as $signature) {
        $args= sizeof($signature);
        $methodax= max($methodax, $args- 1);
        if (isset($cases[$args])) continue;
        
        $cases[$args]= (
          'case '.$args.': '.
          'return $this->'.$this->getHandlerName().'->invoke($this, \''.$method->getName(true).'\', ['.
          ($args ? '$_'.implode(', $_', range(0, $args- 1)) : '').']);'
        );
      }

      // Create method
      $bytes.= (
        'public function '.$method->getName().'($_'.implode('= null, $_', range(0, $methodax)).'= null) { '.
        'switch (func_num_args()) {'.implode("\n", $cases).
        ' default: throw new \lang\IllegalArgumentException(\'Illegal number of arguments\'); }'.
        '}'."\n"
      );
    } else {
      $signature= $args= '';
      foreach ($method->getParameters() as $param) {
        $restriction= $param->getTypeRestriction();
        $signature.= ', '.($restriction ? literal($restriction->getName()) : '').' $'.$param->getName();
        $args.= ', $'.$param->getName();
        $param->isOptional() && $signature.= '= '.var_export($param->getDefaultValue(), true);
      }
      $signature= substr($signature, 2);
      $args= substr($args, 2);

      // Create method
      $bytes.= (
        'public function '.$method->getName().'('.$signature.') { '.
        'return $this->'.$this->getHandlerName().'->invoke($this, \''.$method->getName(true).'\', func_get_args()); '.
        '}'."\n"
      );
    }
    return $bytes;
  }

  /**
   * Creates an XPClass instance from the specified code using a
   * DynamicClassLoader.
   *
   * @param   string bytes
   * @return  lang.XPClass
   */
  private function createClass($bytes) {
    $dyn= \lang\DynamicClassLoader::instanceFor(__METHOD__);
    try {
      $dyn->setClassBytes($this->getProxyName(), $bytes);
      $class= $dyn->loadClass($this->getProxyName());
    } catch (\lang\FormatException $e) {
      throw new \lang\IllegalArgumentException($e->getMessage());
    }
    return $class;
  }
  
  /**
   * Returns an instance of a proxy class for the specified interfaces
   * that dispatches method invocations to the specified invocation
   * handler.
   *
   * @param   lang.ClassLoader classloader
   * @param   lang.XPClass[] interfaces
   * @param   lang.reflect.InvocationHandler handler
   * @return  lang.XPClass
   * @throws  lang.IllegalArgumentException
   */
  public function createProxyInstance($classloader, $interfaces, $handler) {
    return $this->createProxyClass($classloader, $interfaces)->newInstance($handler);
  }
}
