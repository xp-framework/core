<?php namespace lang\codedom;

use lang\FormatException;

class Repeat extends \lang\Object {
  
  public function __construct($rule) {
    $this->rule= $rule;
  }

  public function evaluate($rules, $tokens, &$offset) {
    $s= sizeof($tokens);
    $values= [];
    try {
      do {
        $values[]= $this->rule->evaluate($rules, $tokens, $offset);
      } while ($offset < $s);
    } catch (FormatException $e) {
      // Ends loop
    }
    return $values;
  }
}