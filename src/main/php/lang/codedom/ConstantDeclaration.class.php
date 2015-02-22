<?php namespace lang\codedom;

class ConstantDeclaration extends \lang\Object {
  private $name, $initial;

  public function __construct($name, $initial) {
    $this->name= $name;
    $this->initial= $initial;
  }

  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->name === $cmp->name &&
      $this->initial === $cmp->initial
    );
  }
}