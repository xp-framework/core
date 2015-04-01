<?php namespace lang\codedom;

use util\Objects;

class AnnotationPairs extends \lang\Object {
  private $backing;

  public function __construct($value) {
    $this->backing= $value;
  }

  public function resolve($context, $imports) {
    $resolved= [];
    foreach ($this->backing as $pair) {
      $resolved[key($pair)]= current($pair)->resolve($context, $imports);
    }
    return $resolved;
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'('.Objects::stringOf($this->backing).')';
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && Objects::equal($this->backing, $cmp->backing);
  }
}