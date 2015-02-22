<?php namespace lang\codedom;

class Returns extends Match {
  private $value;
  
  public function __construct($value) {
    $this->value= $value;
  }

  public function consume($rules, $stream, $values) {
    return new Values($this->value);
  }

  public function toString() {
    return $this->getClassName().'@'.\xp::stringOf($this->value);
  }
}