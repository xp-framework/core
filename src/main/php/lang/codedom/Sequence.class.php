<?php namespace lang\codedom;

use lang\FormatException;

class Sequence extends Match {
  
  public function __construct($rules, $func) {
    $this->rules= $rules;
    $this->func= $func;
  }

  public function consume($rules, $stream, $values) {
    $begin= $stream->position();
    foreach ($this->rules as $rule) {
      $result= $rule->consume($rules, $stream, []);
      if ($result->matched()) {
        $values[]= $result->backing();
      } else {
        $stream->reset($begin);
        return new Unmatched($rule, $result);
      }
    }

    $f= $this->func;
    return new Values($f($values));
  }

  public function toString() {
    return $this->getClassName().'@'.\xp::stringOf($this->rules);
  }
}