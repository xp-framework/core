<?php namespace net\xp_framework\unittest\annotations;

class Name implements \lang\Value {
  private $value;

  /** @param string $value */
  public function __construct($value) { $this->value= (string)$value; }

  /** @return string */
  public function value() { return $this->value; }

  /**
   * Returns a hashcode
   *
   * @return string
   */
  public function hashCode() {
    return crc32($this->value);
  }

  /**
   * Compares this name to another
   *
   * @param  var $cmp
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->value <=> $value->value : 1;
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