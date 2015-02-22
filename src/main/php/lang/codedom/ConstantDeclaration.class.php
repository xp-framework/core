<?php namespace lang\codedom;

class ConstantDeclaration extends MemberDeclaration {
  private $initial;

  public function __construct($name, $initial) {
    parent::__construct(0, null, $name);
    $this->initial= $initial;
  }

  /** @return bool */
  public function isConstant() { return true; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf('%s@<%s = %s>', $this->getClassName(), $this->name, $this->initial);
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->name === $cmp->name &&
      $this->initial === $cmp->initial
    );
  }
}