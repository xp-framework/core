<?php namespace lang\codedom;

use lang\reflect\Modifiers;

class FieldDeclaration extends \lang\Object {
  private $modifiers, $name, $initial;

  public function __construct($modifiers, $annotations, $name, $initial) {
    $this->modifiers= $modifiers;
    $this->annotations= $annotations;
    $this->name= $name;
    $this->initial= $initial;
  }

  public function access($modifiers) {
    $this->modifiers= $modifiers;
  }

  public function annotate($annotations) {
    $this->annotations= $annotations;
  }

  public function toString() {
    return sprintf(
      '%s@<%s $%s%s>',
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->initial ? ' = '.$this->initial.';' : ''
    );
  }

  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->modifiers === $cmp->modifiers &&
      $this->annotations === $cmp->annotations &&
      $this->name === $cmp->name &&
      $this->initial === $cmp->initial
    );
  }
}