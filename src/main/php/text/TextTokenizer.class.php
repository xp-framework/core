<?php namespace text;
 
use io\streams\Reader;
use io\IOException;
use lang\IllegalStateException;
 
/**
 * A text tokenizer is a tokenizer that works readers.
 * 
 * Example:
 * ```php
 * $st= new TextTokenizer(new TextReader(new FileInputStream(new File('test.txt')), 'utf-8'), " \n");
 * while ($st->hasMoreTokens()) {
 *   printf("- %s\n", $st->nextToken());
 * }
 * ```
 *
 * @test  xp://net.xp_framework.unittest.text.TextTokenizerTest
 * @see   xp://text.Tokenizer
 */
class TextTokenizer extends Tokenizer {
  protected
    $_stack = [],
    $_buf   = '';

  /**
   * Reset this tokenizer
   *
   */
  public function reset() {
    if ('' !== $this->_buf) {
      try {
        $this->source->reset();
      } catch (IOException $e) {
        throw new IllegalStateException('Cannot reset', $e);
      }
    } 

    $this->_stack= [];
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
        if ($offset < strlen($this->_buf)- 1) break;
        if (null === ($buf= $this->source->read())) {
          break;
        }
        $this->_buf.= $buf;
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
