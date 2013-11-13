<?php namespace unittest\mock\arguments;



/**
 * Argument matcher that uses a user function for matching.
 *
 */
class DynamicMatcher extends \lang\Object implements IArgumentMatcher {
  private
    $function      = null,
    $classOrObject = null;
  
  /**
   * Constructor
   * 
   * @param   string function
   * @param   var classOrObject
   */
  public function __construct($function, $classOrObject= null) {
    $this->function= $function;
    $this->classOrObject= $classOrObject;
  }
  
  /**
   * Trivial matches implementations.
   * 
   * @param   var value
   * @return  bool
   */
  public function matches($value) {
    return call_user_func(array($this->classOrObject, $this->function), $value);
  }
}
