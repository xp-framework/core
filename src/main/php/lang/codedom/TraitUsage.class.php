<?php namespace lang\codedom;

class TraitUsage extends BodyPart {
  private $name;

  /**
   * Creates a new trait usage
   *
   * @param  string $name The trait's name
   */
  public function __construct($name) {
    $this->name= $name;
  }

  /** @return string */
  public function type() { return 'trait'; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf('%s@(use %s)', $this->getClassName(), $this->name);
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && ($this->name === $cmp->name); 
  }
}