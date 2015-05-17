<?php namespace text;
 
/**
 * A string tokenizer allows you to break a string into tokens,
 * these being delimited by any character in the delimiter.
 * 
 * Example:
 * ```php
 * $st= new StringTokenizer("Hello World!\nThis is an example", " \n");
 * while ($st->hasMoreTokens()) {
 *   printf("- %s\n", $st->nextToken());
 * }
 * ```
 *
 * This would output:
 * ```
 * - Hello
 * - World!
 * - This
 * - is
 * - an
 * - example
 * ```
 *
 * @test  xp://net.xp_framework.unittest.text.StringTokenizerTest
 * @see   xp://text.Tokenizer
 */
class StringTokenizer extends Tokenizer {
  protected
    $_stack = [],
    $_ofs   = 0,
    $_len   = 0;

  /**
   * Reset this tokenizer
   *
   */
  public function reset() {
    $this->_stack= [];
    $this->_ofs= 0;
    $this->_len= strlen($this->source);
  }
  
  /**
   * Tests if there are more tokens available
   *
   * @return  bool more tokens
   */
  public function hasMoreTokens() {
    return (!empty($this->_stack) || $this->_ofs < $this->_len);
  }

  /**
   * Push back a string
   *
   * @param   string str
   */
  public function pushBack($str) {
    $this->_ofs= min($this->_ofs, $this->_len);
    $this->source= (
      substr($this->source, 0, $this->_ofs).
      $str.implode('', $this->_stack).
      substr($this->source, $this->_ofs)
    );
    $this->_len= strlen($this->source);
    $this->_stack= [];
  }
      
  /**
   * Returns the next token from this tokenizer's string
   *
   * @param   bool delimiters default NULL
   * @return  string next token
   */
  public function nextToken($delimiters= null) {
    if (empty($this->_stack)) {
      $offset= strcspn($this->source, $delimiters ? $delimiters : $this->delimiters, $this->_ofs);
      if (!$this->returnDelims || $offset > 0) $this->_stack[]= substr($this->source, $this->_ofs, $offset);
      if ($this->returnDelims && $this->_ofs + $offset < $this->_len) {
        $this->_stack[]= $this->source{$this->_ofs + $offset};
      }
      $this->_ofs+= $offset+ 1;
    }
    return array_shift($this->_stack);
  }
}
