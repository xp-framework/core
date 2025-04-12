<?php namespace util;

use util\Objects;

/**
 * Implements object comparison via `hashCode()` and `compareTo()`
 *
 * @see   lang.Value
 * @test  util.unittest.ComparisonTest
 */
trait Comparison {

  /**
   * Calculates a hash code based on type and members
   *
   * @return string
   */
  public function hashCode() {
    $s= get_class($this);
    foreach ((array)$this as $val) {
      $s.= '|'.Objects::hashOf($val);
    }
    return $s;
  }

  /**
   * Performs member-wise comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    if ($value instanceof self) {
      $compare= (array)$value;
      foreach ((array)$this as $key => $val) {
        if (0 !== $r= Objects::compare($val, $compare[$key])) return $r;
      }
      return 0;
    }
    return 1;
  }
}