<?php namespace lang\codedom;

define('T_ANNOTATION', 600);

class Stream extends \lang\Object {
  private $stream, $offset, $length, $comments, $token, $line;

  public function __construct($input) {
    $this->stream= token_get_all($input);
    $this->offset= 0;
    $this->length= sizeof($this->stream);
    $this->comments= [];
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
          $this->comments[]= $next[1];
          $this->line= $next[2];
        } else if (T_COMMENT === $next[0]) {
          if ('#' === $next[1]{0}) {
            $next= $this->annotation();
            $this->line= $this->stream[$this->offset][2];
            $continue= false;
          } else {
            $this->line= $next[2];
            $this->comments[]= $next[1];
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
   * Forwards stream
   *
   * @return bool
   */
  public function next() {
    $this->offset++;
    $this->token= null;
    return $this->offset < $this->length;
  }

  public function position() {
    return $this->offset;
  }

  public function line() {
    return $this->line;
  }

  public function reset($position) {
    if ($position !== $this->offset) {
      $this->offset= $position;
      $this->token= null;
    }
  }

  public function comment() {
    return array_pop($this->comments);
  }
}