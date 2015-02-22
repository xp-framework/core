<?php namespace lang\codedom;

use lang\FormatException;

class OneOf extends Match {
  private $cases;

  public function __construct($cases) {
    $this->cases= $cases;
  }

  public function consume($rules, $stream, $values) {
    $case= $stream->token();
    if (isset($this->cases[$case[0]])) {
      $stream->next();
      return $this->cases[$case[0]]->consume($rules, $stream, [is_array($case) ? $case[1] : $case]);
    }

    return new Unexpected(
      sprintf(
        'Unexpected %s, expecting one of %s',
        is_array($case) ? token_name($case[0]) : $case,
        implode(', ', array_map('token_name', array_keys($this->cases)))
      ),
      $stream->line()
    );
  }
}