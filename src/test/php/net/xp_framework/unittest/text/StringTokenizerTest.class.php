<?php namespace net\xp_framework\unittest\text;

use text\StringTokenizer;

class StringTokenizerTest extends AbstractTokenizerTest {

  /**
   * Retrieve a tokenizer instance
   *
   * @param   string source
   * @param   string delimiters default ' '
   * @param   bool returnDelims default FALSE
   * @return  text.Tokenizer
   */
  protected function tokenizerInstance($source, $delimiters= ' ', $returnDelims= false) {
    return new StringTokenizer($source, $delimiters, $returnDelims);
  }
}
