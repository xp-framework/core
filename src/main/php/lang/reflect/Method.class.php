<?php namespace lang\reflect;

use lang\XPClass;
use lang\IllegalArgumentException;
use lang\IllegalAccessException;

/**
 * Represents a class method
 *
 * @see   xp://lang.XPClass
 * @see   xp://lang.reflect.Routine
 * @test  xp://net.xp_framework.unittest.reflection.MethodsTest
 * @test  xp://net.xp_framework.unittest.reflection.ReflectionTest
 */
class Method extends Routine {

  /**
   * Invokes the underlying method represented by this Method object, 
   * on the specified object with the specified parameters.
   *
   * Example:
   * ```php
   * $method= XPClass::forName('lang.Object')->getMethod('toString');
   * $str= $method->invoke(new Object());
   * ```
   *
   * Example (passing arguments)
   * ```php
   * $method= XPClass::forName('util.Date')->getMethod('format');
   * $str= $method->invoke(Date::now(), ['%d.%m.%Y']);
   * ```
   *
   * Example (static invokation):
   * ```php
   * $method= XPClass::forName('util.log.Logger')->getMethod('getInstance');
   * $log= $method->invoke(null);
   * ```
   *
   * @param   lang.Object $obj
   * @param   var[] $args default []
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case the method is not public or if it is abstract
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked method
   */
  public function invoke($obj, $args= []) {
    if (null !== $obj && !($obj instanceof $this->_class)) {
      throw new IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        XPClass::nameOf($this->_class),
        \xp::typeOf($obj)
      ));
    }
    
    // Check modifiers. If caller is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $this->_reflect->getModifiers();
    if ($m & MODIFIER_ABSTRACT) {
      throw new IllegalAccessException(sprintf(
        'Cannot invoke abstract %s::%s',
        XPClass::nameOf($this->_class),
        $this->_reflect->getName()
      ));
    }
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
          'Cannot invoke %s %s::%s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          XPClass::nameOf($this->_class),
          $this->_reflect->getName(),
          $t[1]['class']
        ));
      }
    }

    try {
      if (!$public) {
        $this->_reflect->setAccessible(true);
      }
      return $this->_reflect->invokeArgs($obj, (array)$args);
    } catch (\lang\SystemExit $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new TargetInvocationException(XPClass::nameOf($this->_class).'::'.$this->_reflect->getName(), $e);
    } catch (\Throwable $e) {
      throw new TargetInvocationException(XPClass::nameOf($this->_class).'::'.$this->_reflect->getName(), $e);
    }
  }
}
