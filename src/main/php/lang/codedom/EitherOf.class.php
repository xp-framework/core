<?php namespace lang\codedom;

use lang\FormatException;

class EitherOf extends \lang\Object {
  
  public function __construct($cases) {
    $this->cases= $cases;
  }

  public function consume($rules, $stream, $values) {
    $begin= $stream->position();
    foreach ($this->cases as $case) {
      $result= $case->consume($rules, $stream, []);
      if ($result->matched()) return $result;
      $stream->reset($begin);
    }

    $token= $stream->token();
    return new Unexpected(
      sprintf(
        'Unexpected %s, expecting one of %s',
        is_array($token) ? token_name($token[0]) : $token,
        \xp::stringOf($this->cases)
      ),
      $stream->line()
    );
  }
}