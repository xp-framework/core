<?php namespace lang\unittest;

use lang\Value;

class Name implements Value {
  private $value;

  /** @param string $value */
  public function __construct($value) { $this->value= (string)$value; }

  /** @return string */
  public function value() { return $this->value; }

  /** @return string */
  public function hashCode() { return crc32($this->value); }

  /** @return string */
  public function toString() { return $this->value; }

  /** @return static */
  public function copy() { return new static($this->value); }

  /**
   * Compares this name to another
   *
   * @param  var $cmp
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->value <=> $value->value : 1;
  }
}