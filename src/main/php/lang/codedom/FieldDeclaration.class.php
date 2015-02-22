<?php namespace lang\codedom;

use lang\reflect\Modifiers;

class FieldDeclaration extends MemberDeclaration {
  private $initial;

  public function __construct($modifiers, $annotations, $name, $initial) {
    parent::__construct($modifiers, $annotations, $name);
    $this->initial= $initial;
  }

  /** @return bool */
  public function isField() { return true; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      '%s@<%s%s $%s%s>',
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->initial ? ' = '.$this->initial : ''
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
      $this->initial === $cmp->initial
    );
  }
}