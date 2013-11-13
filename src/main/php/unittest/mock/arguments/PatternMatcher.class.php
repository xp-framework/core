<?php namespace unittest\mock\arguments;



/**
 * Argument matcher, that uses preg_match for matching.
 *
 */
class PatternMatcher extends \lang\Object implements IArgumentMatcher {
  private 
    $pattern= null;

  /**
   * Constructor
   *
   * @param string pattern
   */
  public function __construct($pattern) {
    $this->pattern= $pattern;
  }

  /**
   * Matches the pattern against the value.
   * 
   * @param   string value
   * @return  bool
   */
  public function matches($value) {
    return preg_match($this->pattern, $value) === 1;
  }
}
