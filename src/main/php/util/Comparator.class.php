<?php namespace util;

/**
 * Comparator interface 
 *
 * @see   php://usort
 */
interface Comparator {

  /**
   * Compares its two arguments for order. Returns a negative integer, 
   * zero, or a positive integer as the first argument is less than, 
   * equal to, or greater than the second.
   *
   * @return int
   */
  public function compare($a, $b);
}
