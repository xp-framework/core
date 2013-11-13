<?php namespace unittest\mock\arguments;

/**
 * Interface for argument matchers
 *
 */
interface IArgumentMatcher {

  /**
   * Checks whether the provided parameter does match a certain criteria.
   * 
   * @param   var value
   * @return  bool
   */
  public function matches($value);
}
