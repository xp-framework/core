<?php namespace lang\codedom;

class TraitUsage extends \lang\Object {
  private $name;

  public function __construct($name) {
    $this->name= $name;
  }

  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->name === $cmp->name
    );
  }
}