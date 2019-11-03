<?php namespace lang\reflect;

use lang\{XPClass, IllegalArgumentException, IllegalAccessException};

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
   * $method= XPClass::forName('lang.Value')->getMethod('toString');
   * $str= $method->invoke(new Date());
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
   * @param   object $obj
   * @param   var[] $args default []
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case the method is not public or if it is abstract
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked method
   */
  public function invoke($obj, $args= []) {
    if (null !== $obj && !($obj instanceof $this->_class)) {
      $d= $this->_reflect->getDeclaringClass();
      if (!$d->isTrait()) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument is not a %s class (%s)',
          XPClass::nameOf($this->_class),
          nameof($obj)
        ));
      }

      $o= new \ReflectionObject($obj);
      if (!in_array($d->getName(), $o->getTraitNames())) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument does not use the trait %s (%s)',
          XPClass::nameOf($this->_class),
          nameof($obj)
        ));
      }

      if ($aliases= $o->getTraitAliases()) {
        $a= array_search($d->getName().'::'.$this->getName(), $aliases);
      } else {
        $a= null;
      }
      $target= $o->getMethod($a ?: $this->_reflect->getName());
    } else {
      $target= $this->_reflect;
    }
    
    // Check modifiers. If caller is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $target->getModifiers();
    if ($m & MODIFIER_ABSTRACT) {
      throw new IllegalAccessException(sprintf(
        'Cannot invoke abstract %s::%s',
        XPClass::nameOf($this->_class),
        $target->getName()
      ));
    }
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(0, 2);
      $decl= $target->getDeclaringClass()->getName();
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
          $target->getName(),
          $t[1]['class']
        ));
      }
    }

    try {
      $public || $target->setAccessible(true);
      return $target->invokeArgs($obj, (array)$args);
    } catch (\lang\SystemExit $e) {
      throw $e;
    } catch (\Throwable $e) {
      throw new TargetInvocationException(XPClass::nameOf($this->_class).'::'.$this->_reflect->getName(), $e);
    }
  }
}
