<?php namespace unittest\mock\arguments;

/**
 * Argument matcher that uses a user function for matching.
 */
class DynamicMatcher extends \lang\Object implements IArgumentMatcher {
  private $invokeable;

  /**
   * Constructor
   *
   * @param   string function
   * @param   var classOrObject
   */
  public function __construct($function, $classOrObject= null) {
    $this->invokeable= $classOrObject ? [$classOrObject, $function] : $function;
  }

  /**
   * Trivial matches implementations.
   *
   * @param   var value
   * @return  bool
   */
  public function matches($value) {
    $inv= $this->invokeable;
    return $inv($value);
  }
}
