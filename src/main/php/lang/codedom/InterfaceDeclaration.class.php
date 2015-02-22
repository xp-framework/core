<?php namespace lang\codedom;

use util\Objects;
use lang\reflect\Modifiers;

class InterfaceDeclaration extends \lang\Object {
  private $modifiers, $annotations, $name, $extends, $body;

  public function __construct($modifiers, $annotations, $name, $extends, $body) {
    $this->modifiers= $modifiers;
    $this->annotations= $annotations;
    $this->name= $name;
    $this->extends= $extends;
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
      "%s@(%s%s %s%s){\n%s}",
      $this->getClassName(),
      $this->annotations ? $this->annotations.' ' : '',
      implode(' ', Modifiers::namesOf($this->modifiers)),
      $this->name,
      $this->extends ? ' extends '.$this->extends : '',
      implode('', array_map(function($decl) { return '  '.str_replace("\n", "\n  ", $decl->toString())."\n"; }, $this->body))
    );
  }

  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->modifiers === $cmp->modifiers &&
      $this->annotations === $cmp->annotations &&
      $this->name === $cmp->name &&
      Objects::equal($this->extends, $cmp->extends) &&
      Objects::equal($this->body, $cmp->body)
    );
  }
}