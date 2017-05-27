<?php namespace util;

use lang\reflect\InvocationHandler;
use lang\Throwable;
use lang\ClassCastException;

/**
 * Lazy initializable InvokationHandler 
 *
 * @test  xp://net.xp_framework.unittest.util.DeferredInvokationHandlerTest
 */
abstract class AbstractDeferredInvokationHandler implements InvocationHandler {
  private $_instance= null;

  /**
   * Lazy initialization callback
   *
   * @return  lang.Generic
   */
  public abstract function initialize();

  /**
   * Processes a method invocation on a proxy instance and returns
   * the result.
   *
   * @param  lang.reflect.Proxy $proxy
   * @param  string $method the method name
   * @param  var... $args an array of arguments
   * @return var
   * @throws util.DeferredInitializationException
   */
  public function invoke($proxy, $method, $args) {
    if (null === $this->_instance) {
      try {
        $this->_instance= $this->initialize();
      } catch (Throwable $e) {
        $this->_instance= null;
        throw new DeferredInitializationException($method, $e);
      }
      if (!is_object($this->_instance)) {
        throw new DeferredInitializationException(
          $method,
          new ClassCastException('Initializer returned '.typeof($this->_instance)->getName())
        );
      }
    }
    return $this->_instance->{$method}(...$args);
  }
} 
