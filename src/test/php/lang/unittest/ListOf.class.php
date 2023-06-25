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
   * Adds an element
   *
   * @param   T... elements
   * @return  lang.unittest.List self
   */
  #[Generic(params: 'T...')]
  public function withAll(... $args) {
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