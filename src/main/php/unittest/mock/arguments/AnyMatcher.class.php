<?php namespace unittest\mock\arguments;



/**
 * Trivial argument matcher, that just returns true.
 *
 */
class AnyMatcher extends \lang\Object implements IArgumentMatcher {

  /**
   * Trivial matches implementations.
   * 
   * @param   var value
   * @return  bool
   */
  public function matches($value) {
    return true;
  }
}
