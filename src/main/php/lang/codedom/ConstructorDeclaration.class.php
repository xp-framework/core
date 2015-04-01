<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class ConstructorDeclaration extends MemberDeclaration {
  private $parameters, $body, $throws;

  /**
   * Creates a new method declaration
   *
   * @param  int $modifiers
   * @param  string $annotations
   * @param  string $name
   * @param  string[] $parameters Argument types
   * @param  string $body Code in body as string
   */
  public function __construct($modifiers, $annotations, $name, $parameters, $throws, $body) {
    parent::__construct($modifiers, $annotations, $name);
    $this->parameters= $parameters;
    $this->throws= $throws;
    $this->body= $body;
  }

  /** @return bool */
  public function isMethod() { return true; }

  /** @return string[] */
  public function parameters() { return $this->parameters; }

  /** @return string */
  public function returns() { return null; }

  /** @return string[] */
  public function throws() { return $this->throws; }

  /** @return string */
  public function body() { return $this->body; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      '%s@<%s%s %s(%s)>%s%s',
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      implode(', ', $this->parameters),
      $this->throws ? ' throws '.implode(' ', $this->throws) : '',
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
      Objects::equal($this->parameters, $cmp->parameters) &&
      Objects::equal($this->throws, $cmp->throws) &&
      Objects::equal($this->body, $cmp->body)
    );
  }
}