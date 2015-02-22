<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class MethodDeclaration extends \lang\Object {
  private $modifiers, $annotations, $name, $arguments, $returns, $body;

  public function __construct($modifiers, $annotations, $name, $arguments, $returns, $body) {
    $this->modifiers= $modifiers;
    $this->annotations= $annotations;
    $this->name= $name;
    $this->arguments= $arguments;
    $this->returns= $returns;
    $this->body= $body;
  }

  public function access($modifiers) {
    $this->modifiers= $modifiers;
  }

  public function annotate($annotations) {
    $this->annotations= $annotations;
  }

  public function toString() {
    return sprintf(
      '%s@<%s%s %s(%s)>%s',
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->arguments,
      $this->body ? ' {'.strlen($this->body).' bytes}' : ''
    );
  }

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