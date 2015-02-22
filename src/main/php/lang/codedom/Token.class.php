<?php namespace lang\codedom;

class Token extends Match {
  private $token;
  
  public function __construct($token) {
    $this->token= $token;
  }

  public function consume($rules, $stream, $values) {
    $token= $stream->token();

    if ($token[0] === $this->token) {
      $stream->next();
      return new Values(is_array($token) ? $token[1] : $token);
    } else {
      return new Unexpected(
        sprintf(
          'Unexpected %s, expecting %s',
          is_array($token) ? token_name($token[0]) : $token,
          is_int($this->token) ? token_name($this->token) : $this->token
        ),
        $stream->line()
      );
    }
  }

  public function toString() {
    return $this->getClassName().'[`'.(is_int($this->token) ? token_name($this->token) : $this->token).'`]';
  }
}