<?php namespace lang\unittest;

use lang\Generic;

#[Generic(self: 'T')]
class Nullable {
  protected $value;

  /**
   * Constructor
   *
   * @param   T value
   */
  #[Generic(params: 'T')]
  public function __construct($value= null) {
    $this->value= $value;
  }

  /**
   * Returns whether a value exists
   *
   * @return  bool
   */
  public function hasValue() {
    return $this->value !== null;
  }

  /**
   * Sets value
   *
   * @param   T value
   * @return  self this instance
   */
  #[Generic(params: 'T')]
  public function set($value= null) {
    $this->value= $value;
    return $this;
  }

  /**
   * Returns value
   *
   * @return  T value
   */
  #[Generic(return: 'T')]
  public function get() {
    return $this->value;
  }
}