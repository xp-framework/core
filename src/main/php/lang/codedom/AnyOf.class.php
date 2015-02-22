<?php namespace lang\codedom;

class AnyOf extends Match {
  private $cases;
  
  public function __construct($cases) {
    $this->cases= $cases;
  }

  public function consume($rules, $stream, $values) {
    $begin= $stream->position();

    $match= true;
    do {
      $case= $stream->token();
      if (isset($this->cases[$case[0]])) {
        $stream->next();
        $result= $this->cases[$case[0]]->consume($rules, $stream, [is_array($case) ? $case[1] : $case]);
        if ($result->matched()) {
          $values[]= $result->backing();
        } else {
          $stream->reset($begin);
          return new Unexpected('Matched beginning but not rest: '.$result->error(), $stream->line());
        }
      } else {
        $match= false;
      }
    } while ($match);

    return new Values($values);
  }
}