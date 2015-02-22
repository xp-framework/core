<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class ClassDeclaration extends TypeDeclaration {
  private $extends, $implements;

  public function __construct($modifiers, $annotations, $name, $extends, $implements, $body) {
    parent::__construct($modifiers, $annotations, $name, $body);
    $this->extends= $extends;
    $this->implements= $implements;
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      "%s@(%s%s %s%s%s){\n%s}",
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->extends ? ' extends '.$this->extends : '',
      $this->implements ? ' implements '.implode(', ', $this->implements) : '',
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
      $this->extends === $cmp->extends &&
      Objects::equal($this->implements, $cmp->implements) &&
      Objects::equal($this->body, $cmp->body)
    );
  }
}