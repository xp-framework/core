<?php namespace lang\codedom;

use util\Objects;

class AnnotationDeclaration extends \lang\Object {
  private $target, $name, $value;

  public function __construct($target, $name, \lang\Generic $value) {
    $this->target= $target;
    $this->name= $name;
    $this->value= $value;
  }

  /** @return string */
  public function target() { return $this->target; }

  /** @return string */
  public function name() { return $this->name; }

  /** @return string */
  public function value() { return $this->value; }

  public function resolve($context, $imports) {
    return $this->value->resolve($context, $imports);
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->target === $cmp->target &&
      $this->name === $cmp->name &&
      Objects::equal($this->value, $cmp->value)
    );
  }
}