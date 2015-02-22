<?php namespace lang\codedom;

class Tokens extends Match {
  private $tokens;
  
  public function __construct($tokens) {
    $this->tokens= $tokens;
  }

  public function consume($rules, $stream, $values) {
    $consumed= [];
    while (in_array($stream->token()[0], $this->tokens)) {
      $consumed[]= $stream->token()[1];
      $stream->next();
    }
    return new Values($consumed);
  }

  private function nameOf($token) { return is_int($token) ? token_name($token) : '`'.$token.'`'; }

  public function toString() {
    return $this->getClassName().'['.implode(' | ', array_map([$this, 'nameOf'], $this->tokens)).']';
  }
}
