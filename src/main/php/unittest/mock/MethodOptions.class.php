<?php namespace unittest\mock;



/**
 * Implements a fluent interface for specifying mock expectation.
 *
 */
class MethodOptions extends \lang\Object implements IMethodOptions {
  private
    $expectation= null,
    $methodName= null;

  /**
   * Constructor
   *
   * @param   unittest.mock.Expectation expectation
   * @param   string methodName
   */
  public function  __construct($expectation, $methodName) {
    if (!($expectation instanceof Expectation)) {
      throw new \lang\IllegalArgumentException('Invalid expectation map passed.');
    }
    if (!($methodName)) {
      throw new \lang\IllegalArgumentException('Method name required.');
    }
    
    $this->expectation= $expectation;
    $this->methodName= $methodName;
  }
  
  /**
   * Specifies the expected return value.
   *
   * @param   var The value that is to be returned on a method call.
   * @return  unittest.mock.IMethodOptions
   */
  public function returns($value) {
    $this->expectation->setReturn($value);
    return $this;
  }

  /**
   * Specifies the exception that is to be thrown.
   *
   * @param   lang.Throwable the exception that is to be thrown on a method call.
   * @return  unittest.mock.IMethodOptions
   */
  public function throws(\lang\Throwable $exception) {
    $this->expectation->setException($exception);
    return $this;
  }

  /**
   * Specifies the number of calls that are expected for the method.
   *
   * @param   int repeatCount
   * @return  unittest.mock.IMethodOptions
   */
  public function repeat($repeatCount) {
    $this->expectation->setRepeat($repeatCount);
    return $this;
  }
  
  /**
   * Specifies that this expection is valid for all calls the method.
   * 
   * @return  unittest.mock.IMethodOptions
   */
  public function repeatAny() {
    return $this->repeat(-1);
  }
  
  /**
   * Defines property behaviour
   *
   * @return  unittest.mock.IMethodOptions
   * @throws  lang.IllegalStateException
   */
  public function propertyBehavior() {
    $prefix= substr($this->methodName, 0, 3);
    if($prefix != 'set' && $prefix != 'get') {
      throw new \lang\IllegalStateException('Property behavior is only applicable to getters and setters.');
    }
    
    $this->expectation->setPropertyBehavior();
    return $this;
  }
}
