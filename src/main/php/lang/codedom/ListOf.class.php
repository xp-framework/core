<?php namespace lang\codedom;

use lang\FormatException;

class ListOf extends Match {
  
  public function __construct($rule) {
    $this->rule= $rule;
  }

  public function consume($rules, $stream, $values) {
    $continue= true;
    do {
      $result= $this->rule->consume($rules, $stream, []);
      if ($result->matched()) {
        $values[]= $result->backing();
        if (',' === $stream->token()) {
          $stream->next();
        } else {
          $continue= false;
        }
      } else {
        return $result;
      }
    } while ($continue);

    return new Values($values);
  }
}