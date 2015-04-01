<?php namespace lang\codedom;

use lang\FormatException;

abstract class Match extends \lang\Object {

  public abstract function consume($rules, $tokens, $values);

  public function evaluate($rules, $tokens) {
    $values= $this->consume($rules, $tokens, []);
    if ($values->matched()) {
      return $values->backing();
    } else {
      throw new FormatException($values->error());
    }
  }
}