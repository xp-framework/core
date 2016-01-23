<?php namespace net\xp_framework\unittest\core\generics;

/**
 * List of elements
 *
 */
#[@generic(self= 'T')]
class ListOf extends \lang\Object {
  public $elements= [];

  /**
   * Constructor
   *
   * @param   T... initial
   */
  #[@generic(params= 'T...')]
  public function __construct(... $args) {
    $this->elements= $args;
  }

  /**
   * Adds an element
   *
   * @param   T... elements
   * @return  net.xp_framework.unittest.core.generics.List self
   */
  #[@generic(params= 'T...')]
  public function withAll(... $args) {
    $this->elements= array_merge($this->elements, $args);
    return $this;
  }

  /**
   * Returns a list of all elements
   *
   * @return  T[] elements
   */
  #[@generic(return= 'T[]')]
  public function elements() {
    return $this->elements;
  }
}
