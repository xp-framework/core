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
   * @param   T element
   * @return  self
   */
  #[Generic(params: 'T')]
  public function append($element) {
    $this->elements[]= $element;
    return $this;
  }

  /**
   * Extends this list with all given arguments
   *
   * @param   T[] elements
   * @return  self
   */
  #[Generic(params: 'T[]')]
  public function extend($elements) {
    $this->elements= array_merge($this->elements, $elements);
    return $this;
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
   * Applies a given map function to all elements in this list,
   * returning a new list with the mapped elements.
   */
  #[Generic(self: 'M', params: 'function(T): M', return: 'self<M>')]
  public function map($map) {
    $m= create("new lang.unittest.ListOf<$M>");
    foreach ($this->elements as $element) {
      $m->elements[]= $map($element);
    }
    return $m;
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