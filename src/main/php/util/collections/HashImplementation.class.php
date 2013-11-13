<?php namespace util\collections;

/**
 * Hash implementation
 *
 * @see   xp://util.collections.HashProvider
 */
interface HashImplementation {

  /**
   * Retrieve hash code for a given string
   *
   * @param   string $str
   * @return  int hashcode
   */
  public function hashOf($str);

}
