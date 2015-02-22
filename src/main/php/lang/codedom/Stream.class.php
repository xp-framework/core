<?php namespace lang\codedom;

define('T_ANNOTATION', 600);

class Stream extends \lang\Object {
  private $stream, $offset, $length, $comment, $token, $line;

  public function __construct($input) {
    $this->stream= token_get_all($input);
    $this->offset= 0;
    $this->length= sizeof($this->stream);
    $this->comment= null;
    $this->token= null;
    $this->line= 1;
  }

  private function annotation() {
    static $tokens= [T_COMMENT, T_WHITESPACE];

    $annotation= '';
    do {
      $annotation.= trim(substr($this->stream[$this->offset][1], 1));
      $this->offset++;
    } while ($this->offset < $this->length && in_array($this->stream[$this->offset][0], $tokens));

    $this->offset--;
    return [T_ANNOTATION, $annotation];
  }

  /**
   * Gets token
   *
   * @param  bool $whitespace Whether to return whitespace (default: No)
   * @return string
   */
  public function token($whitespace= false) {
    if (null === $this->token) {
      $continue= true;

      do {
        $next= $this->stream[$this->offset];
        if (T_DOC_COMMENT === $next[0]) {
          $this->comment= $next[1];
          $this->line= $next[2];
        } else if (T_COMMENT === $next[0]) {
          if ('#' === $next[1]{0}) {
            $next= $this->annotation();
            $this->line= $this->stream[$this->offset][2];
            $continue= false;
          } else {
            $this->line= $next[2];
          }
        } else if (T_WHITESPACE === $next[0]) {
          $this->line= $next[2];
          $continue= !$whitespace;
        } else {
          $continue= false;
        }
      } while ($continue && $this->offset++ < $this->length);

      $this->token= $next;
    }

    // echo "@$this->offset TOKEN ", is_array($this->token) ? token_name($this->token[0]).'('.$this->token[0].': '.$this->token[1].')' : '`'.$this->token.'`', "\n";
    return $this->token;
  }

  /**
   * Gets last comment
   *
   * @return string
   */
  public function comment() {
    $comment= $this->comment;
    $this->comment= null;
    return $comment;
  }

  /** @return int */
  public function line() { return $this->line; }

  /** @return int */
  public function position() { return $this->offset; }

  /**
   * Forwards stream
   *
   * @return bool
   */
  public function next() {
    $this->offset++;
    $this->token= null;
    return $this->offset < $this->length;
  }

  /**
   * Resets stream position to a given offset, which e.g. was previously
   * retrieved via position()
   *
   * @param  int $position
   */
  public function reset($position) {
    if ($position !== $this->offset) {
      $this->offset= $position;
      $this->token= null;
    }
  }
}