<?php namespace text;
 
/**
 * A stream tokenizer is a tokenizer that works on streams.
 * 
 * Example:
 * ```php
 * $st= new StreamTokenizer(new FileInputStream(new File('test.txt')), " \n");
 * while ($st->hasMoreTokens()) {
 *   printf("- %s\n", $st->nextToken());
 * }
 * ```
 *
 * @test     xp://net.xp_framework.unittest.text.StreamTokenizerTest
 * @see      xp://text.Tokenizer
 */
class StreamTokenizer extends Tokenizer {
  protected
    $_stack = [],
    $_buf   = '',
    $_src   = null;

  /**
   * Reset this tokenizer
   *
   */
  public function reset() {
    $this->_stack= [];
    
    if ($this->_src) {
      if ($this->_src instanceof \io\streams\Seekable) {
        $this->_src->seek(0, SEEK_SET);
      } else {
        throw new \lang\IllegalStateException(
          'Cannot reset, Source '.\xp::stringOf($this->_src).' is not seekable'
        );
      }
    }
    $this->_src= $this->source;
    $this->_buf= '';
  }
  
  /**
   * Tests if there are more tokens available
   *
   * @return  bool more tokens
   */
  public function hasMoreTokens() {
    return !(empty($this->_stack) && false === $this->_buf);
  }
  
  /**
   * Push back a string
   *
   * @param   string str
   */
  public function pushBack($str) {
    $this->_buf= $str.implode('', $this->_stack).$this->_buf;
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
    
      // Read until we have either find a delimiter or until we have 
      // consumed the entire content.
      do {
        $offset= strcspn($this->_buf, $delimiters ? $delimiters : $this->delimiters);
        if ($offset < strlen($this->_buf)- 1 || !$this->_src->available()) break;
        $this->_buf.= $this->_src->read();
      } while (true);

      if (!$this->returnDelims || $offset > 0) $this->_stack[]= substr($this->_buf, 0, $offset);
      $l= strlen($this->_buf);
      if ($this->returnDelims && $offset < $l) {
        $this->_stack[]= $this->_buf{$offset};
      }
      $offset++;
      $this->_buf= $offset < $l ? substr($this->_buf, $offset) : false;
    }
    
    return array_shift($this->_stack);
  }
}
