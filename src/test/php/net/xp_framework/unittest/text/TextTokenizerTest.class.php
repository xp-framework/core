<?php namespace net\xp_framework\unittest\text;

use io\streams\TextReader;
use io\streams\MemoryInputStream;
use text\TextTokenizer;

class TextTokenizerTest extends AbstractTokenizerTest {

  /**
   * Retrieve a tokenizer instance
   *
   * @param   string source
   * @param   string delimiters default ' '
   * @param   bool returnDelims default FALSE
   * @return  text.Tokenizer
   */
  protected function tokenizerInstance($source, $delimiters= ' ', $returnDelims= false) {
    return new TextTokenizer(new TextReader(new MemoryInputStream($source)), $delimiters, $returnDelims);
  }
}
