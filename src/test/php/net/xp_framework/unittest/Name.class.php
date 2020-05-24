<?php namespace net\xp_framework\unittest;

use lang\Value;

class Name implements Value {
  private $value;

  /** @param string $value */
  public function __construct($value) { $this->value= (string)$value; }

  /** @return string */
  public function value() { return $this->value; }

  /** @return static */
  public function copy() { return new static($this->value); }

  /**
   * Compares this name
   *
   * @param  var $cmp
   * @return int
   */
  public function compareTo($cmp) {
    return $cmp instanceof self ? strcmp($this->value, $cmp->value) : -1;
  }

  /**
   * Returns a hashcode
   *
   * @return string
   */
  public function hashCode() {
    return crc32($this->value);
  }

  /**
   * Returns a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->value;
  }
}