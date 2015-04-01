<?php namespace lang\codedom;

class AnyOf extends Match {
  private $detect, $try;

  public function __construct($detect, $try= []) {
    $this->detect= $detect;
    $this->try= $try;
  }

  private static function tokenName($token) {
    return is_int($token) ? token_name($token) : '`'.$token.'`';
  }

  public function consume($rules, $stream, $values) {
    $case= $stream->token();
    if (isset($this->detect[$case[0]])) {
      $stream->next();
      return $this->detect[$case[0]]->consume($rules, $stream, [is_array($case) ? $case[1] : $case]);
    } else {
      $begin= $stream->position();
      foreach ($this->try as $try) {
        $result= $try->consume($rules, $stream, []);
        if ($result->matched()) return $result;
        $stream->reset($begin);
      }
    }

    return new Unexpected(
      sprintf(
        'Unexpected %s, expecting one of %s or %s',
        is_array($case) ? token_name($case[0]) : $case,
        implode(', ', array_map(['self', 'tokenName'], array_keys($this->detect))),
        implode(', ', array_map(['xp', 'stringOf'], array_keys($this->try)))
      ),
      $stream->line()
    );
  }
}