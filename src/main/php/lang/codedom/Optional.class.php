<?php namespace lang\codedom;

use lang\FormatException;

class Optional extends Match {
  private $rule;
  
  public function __construct($rule) {
    $this->rule= $rule;
  }

  public function consume($rules, $stream, $values) {
    $begin= $stream->position();

    $result= $this->rule->consume($rules, $stream, $values);
    if ($result->matched()) {
      return $result;
    } else {
      $stream->reset($begin);
      return new Values(null);
    }
  }
}