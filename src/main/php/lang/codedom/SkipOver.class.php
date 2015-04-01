<?php namespace lang\codedom;

class SkipOver extends Match {
  private $open, $close;

  public function __construct($open, $close) {
    $this->open= $open;
    $this->close= $close;
  }

  public function consume($rules, $stream, $values) {
    $braces= 1;
    $block= implode('', $values);
    do {
      $token= $stream->token(true);
      if ($this->open === $token) {
        $braces++;
        $block.= $this->open;
      } else if ($this->close === $token) {
        $braces--;
        if ($braces <= 0) {
          $stream->next();
          return new Values(trim($block));
        }
        $block.= $this->close;
      } else {
        $block.= is_array($token) ? $token[1] : $token;
      }
    } while ($stream->next());

    return new Unexpected('End of file', $stream->line());
  }

  public function toString() { return $this->getClassName().'[`'.$this->open.'`...`'.$this->close.'`]'; }
}