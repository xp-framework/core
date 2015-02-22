<?php namespace lang\codedom;

use lang\FormatException;

class Repeated extends Match {
  
  public function __construct($rule) {
    $this->rule= $rule;
  }

  public function consume($rules, $tokens, $values) {
    $continue= true;
    do {
      $result= $this->rule->consume($rules, $tokens, []);
      if ($result->matched()) {
        $values[]= $result->backing();
      } else {
        $continue= false;
      }
    } while ($continue);

    return new Values($values);
  }
}