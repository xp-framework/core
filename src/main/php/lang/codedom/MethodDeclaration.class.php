<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class MethodDeclaration extends MemberDeclaration {
  private $arguments, $returns, $body;

  public function __construct($modifiers, $annotations, $name, $arguments, $returns, $body) {
    parent::__construct($modifiers, $annotations, $name);
    $this->arguments= $arguments;
    $this->returns= $returns;
    $this->body= $body;
  }

  /** @return bool */
  public function isMethod() { return true; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      '%s@<%s%s %s(%s)>%s',
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->arguments,
      $this->body ? ' { '.strlen($this->body).' bytes }' : ''
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
      $this->returns === $cmp->returns &&
      Objects::equal($this->arguments, $cmp->arguments) &&
      Objects::equal($this->body, $cmp->body)
    );
  }
}