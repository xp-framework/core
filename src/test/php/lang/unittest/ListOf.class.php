<?php namespace lang\unittest;

use lang\Generic;

#[Generic(self: 'T')]
class ListOf {
  public $elements= [];

  /**
   * Constructor
   *
   * @param   T... initial
   */
  #[Generic(params: 'T...')]
  public function __construct(... $args) {
    $this->elements= $args;
  }

  /**
   * Extends this list with all given arguments
   *
   * @param   T[] elements
   * @return  self
   */
  #[Generic(params: 'T[]')]
  public function extend($args) {
    $this->elements= array_merge($this->elements, $args);
    return $this;
  }

  /**
   * Returns a list of all elements
   *
   * @return  T[] elements
   */
  #[Generic(return: 'T[]')]
  public function elements() {
    return $this->elements;
  }
}