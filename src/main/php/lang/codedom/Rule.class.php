<?php namespace lang\codedom;

use lang\FormatException;

class Rule extends Match {
  private $name;

  public function __construct($name) {
    $this->name= $name;
  }

  public function consume($rules, $tokens, $values) {
    if (isset($rules[$this->name])) {
      return $rules[$this->name]->consume($rules, $tokens, []);
    }

    throw new FormatException('Unknown rule '.$this->name);
  }

  public function toString() {
    return $this->getClassName().'(->'.$this->name.')';
  }
}