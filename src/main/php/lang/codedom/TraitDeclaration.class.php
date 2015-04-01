<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class TraitDeclaration extends TypeDeclaration {

  public function __construct($modifiers, $annotations, $name, $body) {
    parent::__construct($modifiers, $annotations, $name, $body);
  }

  /** @return bool */
  public function isTrait() { return true; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      "%s@(%s%s %s){\n%s}",
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->body->toString('  ')
    );
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->modifiers === $cmp->modifiers &&
      $this->annotations === $cmp->annotations &&
      $this->name === $cmp->name &&
      Objects::equal($this->body, $cmp->body)
    );
  }
}