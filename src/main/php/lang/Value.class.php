<?php namespace lang;

/**
 * Implementing the value interface marks a value object, which may be compared,
 * hashed, and represented as a string.
 */
interface Value {

  /**
   * Compares this value to a given other value. Returns 0 if the values are
   * equal, a negative integer if `$this < $cmp`, and a positive integer if 
   * `$this > $cmp`.
   *
   * @see    https://wiki.php.net/rfc/comparable
   * @param  var $cmp
   * @return int
   */
  public function compareTo($cmp);

  /**
   * Calculates a hashcode and makes this value useable in e.g. collections.
   *
   * @see    php://spl_object_hash
   * @return string
   */
  public function hashCode();

  /**
   * Creates a string representation of this value.
   *
   * @return string
   */
  public function toString();
}