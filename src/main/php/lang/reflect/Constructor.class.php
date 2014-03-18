<?php namespace lang\reflect;



/**
 * Represents a class' constructor
 *
 * @see   xp://lang.XPClass
 * @see   xp://lang.reflect.Routine
 * @test  xp://net.xp_framework.unittest.reflection.ReflectionTest
 */
class Constructor extends Routine {

  /**
   * Uses the constructor represented by this Constructor object to create 
   * and initialize a new instance of the constructor's declaring class, 
   * with the specified initialization parameters.
   *
   * Example:
   * <code>
   *   $constructor= XPClass::forName('util.Binford')->getConstructor();
   *
   *   $instance= $constructor->newInstance();
   *   $instance= $constructor->newInstance(array(6100));
   * </code>
   *
   * @param   var[] args
   * @return  lang.Generic
   * @throws  lang.IllegalAccessException in case the constructor is not public or if it is abstract
   * @throws  lang.reflect.TargetInvocationException in case the constructor throws an exception
   */
  public function newInstance(array $args= []) {

    // Check whether class is abstract
    $class= new \ReflectionClass($this->_class);
    if ($class->isAbstract()) {
      throw new \lang\IllegalAccessException('Cannot instantiate abstract class '.$this->_class);
    }

    // Check modifiers. If caller is an instance of this class, allow private and
    // protected constructor invocation (which the PHP reflection API does not).
    $m= $this->_reflect->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(0, 2);
      $decl= $this->_reflect->getDeclaringClass()->getName();
      if ($m & MODIFIER_PROTECTED) {
        $allow= $t[1]['class'] === $decl || is_subclass_of($t[1]['class'], $decl);
      } else {
        $allow= $t[1]['class'] === $decl;
      }
      if (!$allow) {
        throw new \lang\IllegalAccessException(sprintf(
          'Cannot invoke %s constructor of class %s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          $this->_class,
          $t[1]['class']
        ));
      }
    }

    // For non-public constructors: Use setAccessible() / invokeArgs() combination 
    try {
      if ($public) {
        return $class->newInstanceArgs($args);
      }

      $instance= unserialize('O:'.strlen($this->_class).':"'.$this->_class.'":0:{}');
      $this->_reflect->setAccessible(true);
      $this->_reflect->invokeArgs($instance, $args);
      return $instance;
    } catch (\lang\SystemExit $e) {
      throw $e;
    } catch (\lang\Throwable $e) {
      throw new TargetInvocationException($this->_class.'::<init>', $e);
    } catch (\Exception $e) {
      throw new TargetInvocationException($this->_class.'::<init>', new \lang\XPException($e->getMessage()));
    }
  }

  /**
   * Retrieve return type
   *
   * @return  lang.Type
   */
  public function getReturnType() {
    return \lang\XPClass::forName(\xp::nameOf($this->_class));
  }

  /**
   * Retrieve return type
   *
   * @return  string
   */
  public function getReturnTypeName() {
    return \xp::nameOf($this->_class);
  }
}
