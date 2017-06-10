<?php namespace io\streams;

use lang\IllegalArgumentException;
use io\Channel;

/**
 * Writes text from to underlying output stream, encoding to the
 * given character set.
 *
 * @test  xp://net.xp_framework.unittest.io.streams.TextWriterTest
 * @ext   iconv
 */
class TextWriter extends Writer {
  private $charset;

  /**
   * Constructor. Creates a new TextWriter on an underlying output
   * stream with a given charset.
   *
   * @param   io.streams.OutputStream|io.Channel $arg The target
   * @param   string $charset the character set to encode to.
   * @throws  lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= \xp::ENCODING) {
    if ($arg instanceof OutputStream) {
      $target= $arg;
    } else if ($arg instanceof Channel) {
      $target= $arg->out();
    } else {
      throw new IllegalArgumentException('Given argument is neither an input stream, a channel nor a string: '.typeof($arg)->getName());
    }

    $this->charset= $charset;
    parent::__construct($target);
  }

  /**
   * Returns the character set used
   *
   * @return string
   */
  public function charset() { return $this->charset; }

  /**
   * Writes a BOM
   *
   * @see    http://de.wikipedia.org/wiki/Byte_Order_Mark
   * @see    http://unicode.org/faq/utf_bom.html
   * @return self
   */
  public function withBom() {
    switch (strtolower($this->charset)) {
      case 'utf-16be': $this->stream->write("\376\377"); break;
      case 'utf-16le': $this->stream->write("\377\376"); break;
      case 'utf-8': $this->stream->write("\357\273\277"); break;
    }
    return $this;
  }

  /**
   * Sets newLine property's bytes
   *
   * @deprecated Use withNewLine() instead
   * @param   string newLine
   */
  public function setNewLine($newLine) {
    $this->newLine= $newLine;
  }
  
  /**
   * Gets newLine property's bytes
   *
   * @deprecated Use newLine() instead
   * @return  string newLine
   */
  public function getNewLine() {
    return $this->newLine;
  }

  /**
   * Writes text
   *
   * @param  string $text
   * @return int
   */
  protected function write0($text) {
    return $this->stream->write(iconv(\xp::ENCODING, $this->charset, $text));
  }
}
