<?php namespace lang\reflect;

use lang\IllegalStateException;
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
  public $invoke0;

  /**
   * Constructor
   *
   * @param   string $class The effective class
   * @param   php.ReflectionMethod $reflect
   * @param   var $invoke0 A function
   */
  public function __construct($class, $reflect, $invoke0= null) {
    parent::__construct($class, $reflect);
    $this->invoke0= $invoke0 ?: function($obj, $args) {
      return $this->_reflect->invokeArgs($obj, $args);
    };
  }

  /** @return string[] */
  protected function genericParameters() {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass()->getName(), $this->_reflect->getName());
    if (isset($details[DETAIL_ANNOTATIONS]['generic']['self'])) {
      return explode(',', $details[DETAIL_ANNOTATIONS]['generic']['self']);
    }
    return [];
  }

  /**
   * Retrieve whether this method is generic
   *
   * @return  bool
   */
  public function isGeneric() {
    return sizeof($this->genericParameters()) > 0;
  }

  /**
   * Reflectively creates a new method
   *
   * @param   lang.Type[] arguments
   * @return  lang.reflect.Method
   * @throws  lang.IllegalStateException if this class is not a generic definition
   * @throws  lang.IllegalArgumentException if number of arguments does not match components
   */
  public function newGenericMethod($arguments) {
    $components= $this->genericParameters();
    if (empty($components)) {
      throw new IllegalStateException('Method '.$this->_reflect->name.' is not generic');
    }

    $cs= sizeof($components);
    if ($cs !== sizeof($arguments)) {
      throw new IllegalArgumentException(sprintf(
        'Method %s expects %d component(s) <%s>, %d argument(s) given',
        $this->_reflect->name,
        $cs,
        implode(', ', $components),
        sizeof($arguments)
      ));
    }

    $verify= $placeholders= [];
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass()->getName(), $this->_reflect->getName());
    foreach ($components as $i => $placeholder) {
      $placeholders[$placeholder]= $arguments[$i]->getName();
    }
    foreach (explode(',', @$details[DETAIL_ANNOTATIONS]['generic']['params']) as $placeholder) {
      $verify[]= strtr(ltrim($placeholder), $placeholders);
    }

    return new self($this->_class, $this->_reflect, function($obj, $args) use($arguments, $verify) {
      foreach ($args as $i => $arg) {
        if ($verify[$i] && !is($verify[$i], $arg)) throw new IllegalArgumentException(sprintf(
          'Argument %d passed to %s must be of %s, %s given',
          $i,
          $this->_reflect->name,
          $verify[$i],
          \xp::typeOf($arg)
        ));
      }
      return $this->_reflect->invokeArgs($obj, array_merge($arguments, $args));
    });
  }

  /**
   * Invokes the underlying method represented by this Method object, 
   * on the specified object with the specified parameters.
   *
   * Example:
   * <code>
   *   $method= XPClass::forName('lang.Object')->getMethod('toString');
   *
   *   var_dump($method->invoke(new Object()));
   * </code>
   *
   * Example (passing arguments)
   * <code>
   *   $method= XPClass::forName('lang.types.String')->getMethod('concat');
   *
   *   var_dump($method->invoke(new String('Hello'), ['World']));
   * </code>
   *
   * Example (static invokation):
   * <code>
   *   $method= XPClass::forName('util.log.Logger')->getMethod('getInstance');
   *
   *   var_dump($method->invoke(NULL));
   * </code>
   *
   * @param   lang.Object obj
   * @param   var[] args default []
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case the method is not public or if it is abstract
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked method
   */
  public function invoke($obj, $args= []) {
    if (null !== $obj && !($obj instanceof $this->_class)) {
      throw new IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        \xp::nameOf($this->_class),
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
        $this->_class,
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
          $this->_class,
          $this->_reflect->getName(),
          $t[1]['class']
        ));
      }
    }

    // For non-public methods: Use setAccessible() / invokeArgs() combination 
    // if possible, resort to __call() workaround.
    try {
      if (!$public) {
        $this->_reflect->setAccessible(true);
      }
      return $this->invoke0->__invoke($obj, (array)$args);
    } catch (\lang\SystemExit $e) {
      throw $e;
    } catch (\lang\Throwable $e) {
      throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName(), $e);
    } catch (\Exception $e) {
      throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName(), new \lang\XPException($e->getMessage()));
    }
  }
}
