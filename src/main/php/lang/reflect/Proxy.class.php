<?php namespace lang\reflect;

use lang\DynamicClassLoader;
use lang\IClassLoader;
use lang\IllegalArgumentException;
use lang\FormatException;

/**
 * Proxy provides static methods for creating dynamic proxy
 * classes and instances, and it is also the superclass of all
 * dynamic proxy classes created by those methods.
 *
 * @test  xp://net.xp_framework.unittest.reflection.ProxyTest
 * @see   http://docs.oracle.com/javase/8/docs/api/java/lang/reflect/Proxy.html
 */
class Proxy {
  const PREFIX  = 'Proxy$';
  const LITERAL = "Proxy\xb7";

  protected $_h= null;

  /**
   * Constructor
   *
   * @param   lang.reflect.InvocationHandler handler
   */
  public function __construct($handler) {
    $this->_h= $handler;
  }
  
  /**
   * Returns the XPClass object for a proxy class given a class loader 
   * and an array of interfaces.  The proxy class will be defined by the 
   * specified class loader and will implement all of the supplied 
   * interfaces (also loaded by the classloader).
   *
   * @param   lang.IClassLoader classloader
   * @param   lang.XPClass[] interfaces names of the interfaces to implement
   * @return  lang.XPClass
   * @throws  lang.IllegalArgumentException
   */
  public static function getProxyClass(IClassLoader $classloader, array $interfaces) {
    static $num= 0;
    static $cache= [];
    
    $t= sizeof($interfaces);
    if (0 === $t) {
      throw new IllegalArgumentException('Interfaces may not be empty');
    }
    
    // Calculate cache key (composed of the names of all interfaces)
    $key= $classloader->hashCode().':'.implode(';', array_map(
      function($i) { return $i->getName(); }, 
      $interfaces
    ));
    if (isset($cache[$key])) return $cache[$key];
    
    // Create proxy class' name, using a unique identifier and a prefix
    $decl= self::LITERAL.$num;
    $name= self::PREFIX.$num;
    $bytes= 'class '.$decl.' extends \lang\reflect\Proxy implements ';
    $added= [];
    
    for ($j= 0; $j < $t; $j++) {
      $bytes.= $interfaces[$j]->literal().', ';
    }
    $bytes= substr($bytes, 0, -2)." {\n";

    for ($j= 0; $j < $t; $j++) {
      $if= $interfaces[$j];
      
      // Verify that the Class object actually represents an interface
      if (!$if->isInterface()) {
        throw new IllegalArgumentException($if->getName().' is not an interface');
      }
      
      // Implement all the interface's methods
      foreach ($if->getMethods() as $m) {
      
        // Check for already declared methods, do not redeclare them
        if (isset($added[$m->getName()])) continue;
        $added[$m->getName()]= true;

        // Build signature and argument list
        if ($m->hasAnnotation('overloaded')) {
          $signatures= $m->getAnnotation('overloaded', 'signatures');
          $max= 0;
          $cases= [];
          foreach ($signatures as $signature) {
            $args= sizeof($signature);
            $max= max($max, $args- 1);
            if (isset($cases[$args])) continue;
            
            $cases[$args]= (
              'case '.$args.': '.
              'return $this->_h->invoke($this, \''.$m->getName(true).'\', ['.
              ($args ? '$_'.implode(', $_', range(0, $args- 1)) : '').']);'
            );
          }

          // Create method
          $bytes.= (
            'function '.$m->getName().'($_'.implode('= NULL, $_', range(0, $max)).'= NULL) { '.
            'switch (func_num_args()) {'.implode("\n", $cases).
            ' default: throw new IllegalArgumentException(\'Illegal number of arguments\'); }'.
            '}'."\n"
          );
        } else {
          $signature= $args= '';
          foreach ($m->getParameters() as $param) {
            $restriction= $param->getTypeRestriction();
            $signature.= ', '.($restriction ? literal($restriction->getName()) : '').' $'.$param->getName();
            $args.= ', $'.$param->getName();
            $param->isOptional() && $signature.= '= '.var_export($param->getDefaultValue(), true);
          }
          $signature= substr($signature, 2);
          $args= substr($args, 2);

          // Create method
          $bytes.= (
            'function '.$m->getName().'('.$signature.') { '.
            'return $this->_h->invoke($this, \''.$m->getName(true).'\', ['.$args.']); '.
            '}'."\n"
          );
        }
      }
    }
    $bytes.= ' }';

    // Define the generated class
    \xp::$cn[$decl]= $name;
    try {
      $dyn= DynamicClassLoader::instanceFor(__METHOD__);
      $dyn->setClassBytes($decl, $bytes);
      $class= $dyn->loadClass($decl);
    } catch (FormatException $e) {
      unset(\xp::$cn[$decl]);
      throw new IllegalArgumentException($e->getMessage());
    }

    // Update cache and return XPClass object
    $cache[$key]= $class;
    $num++;
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
  public static function newProxyInstance($classloader, $interfaces, $handler) {
    return self::getProxyClass($classloader, $interfaces)->newInstance($handler);
  }
}
