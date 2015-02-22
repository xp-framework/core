<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class InterfaceDeclaration extends TypeDeclaration {
  private $parents;

  public function __construct($modifiers, $annotations, $name, $parents, $body) {
    parent::__construct($modifiers, $annotations, $name, $body);
    $this->parents= $parents;
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      "%s@(%s%s %s%s){\n%s}",
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->parents ? ' extends '.$this->parents : '',
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
      Objects::equal($this->parents, $cmp->parents) &&
      Objects::equal($this->body, $cmp->body)
    );
  }
}