<?php namespace lang\codedom;

use util\Objects;

class AnnotationValue extends \lang\Object {
  private $backing;

  public function __construct($value) {
    $this->backing= $value;
  }

  public function resolve($context, $imports) {
    return $this->backing;
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