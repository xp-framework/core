<?php namespace lang\codedom;

class ConstantDeclaration extends MemberDeclaration {
  private $value;

  /**
   * Creates a new constant declaration
   *
   * @param  string $name
   * @param  string $value
   */
  public function __construct($name, $value) {
    parent::__construct(0, null, $name);
    $this->value= $value;
  }

  /** @return bool */
  public function isConstant() { return true; }

  /** @return string */
  public function value() { return $this->value; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf('%s@<%s = %s>', $this->getClassName(), $this->name, $this->value);
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
      $this->value === $cmp->value
    );
  }
}