<?php namespace lang\reflect;

use lang\{Type, XPClass};
use lang\IllegalAccessException;

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
   * ```php
   * $constructor= XPClass::forName('util.Binford')->getConstructor();
   *
   * $instance= $constructor->newInstance();
   * $instance= $constructor->newInstance([6100]);
   * ```
   *
   * @param   var[] args
   * @return  object
   * @throws  lang.IllegalAccessException in case the constructor is not public or if it is abstract
   * @throws  lang.reflect.TargetInvocationException in case the constructor throws an exception
   */
  public function newInstance(array $args= []) {
    $class= new \ReflectionClass($this->_class);
    if ($class->isAbstract()) {
      throw new IllegalAccessException('Cannot instantiate abstract class '.XPClass::nameOf($this->_class));
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
        throw new IllegalAccessException(sprintf(
          'Cannot invoke %s constructor of class %s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          XPClass::nameOf($this->_class),
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
    } catch (\Throwable $e) {
      throw new TargetInvocationException(XPClass::nameOf($this->_class).'::<init>', $e);
    }
  }

  /** Retrieve return type */
  public function getReturnType(): Type { return new XPClass($this->_class); }

  /** Retrieve return type name */
  public function getReturnTypeName(): string { return XPClass::nameOf($this->_class); }
}
