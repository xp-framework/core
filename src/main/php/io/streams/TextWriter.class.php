<?php namespace io\streams;

use lang\IllegalArgumentException;
use io\Channel;

/**
 * Writes text from to underlying output stream. When writing lines,
 * uses the newLine property's bytes as newline separator, defaulting
 * to "\n".
 *
 * @test    xp://net.xp_framework.unittest.io.streams.TextWriterTest
 * @ext     iconv
 */
class TextWriter extends Writer {
  private $charset= '';
  private $newLine= "\n";

  /**
   * Constructor. Creates a new TextWriter on an underlying output
   * stream with a given charset.
   *
   * @param   io.streams.OutputStream|io.Channel $arg The target
   * @param   string $charset the charset the stream is encoded in.
   * @throws  lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= \xp::ENCODING) {
    if ($arg instanceof OutputStream) {
      parent::__construct($arg);
    } else if ($arg instanceof Channel) {
      parent::__construct($arg->out());
    } else {
      throw new IllegalArgumentException('Given argument is neither an input stream, a channel nor a string: '.\xp::typeOf($arg));
    }
    $this->charset= $charset;
  }

  /**
   * Writes a BOM
   *
   * @see     http://de.wikipedia.org/wiki/Byte_Order_Mark
   * @see     http://unicode.org/faq/utf_bom.html
   * @return  io.streams.TextWriter this
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
   * @param   string newLine
   */
  public function setNewLine($newLine) {
    $this->newLine= $newLine;
  }
  
  /**
   * Sets newLine property's bytes and returns this writer
   *
   * @param   string newLine
   * @return  io.streams.TextWriter this
   */
  public function withNewLine($newLine) {
    $this->newLine= $newLine;
    return $this;
  }

  /**
   * Gets newLine property's bytes
   *
   * @return  string newLine
   */
  public function getNewLine() {
    return $this->newLine;
  }

  /**
   * Write characters
   *
   * @param   string text
   */
  public function write($text) {
    $this->stream->write(iconv(\xp::ENCODING, $this->charset, $text));
  }
  
  /**
   * Write an entire line
   *
   * @param   string text
   */
  public function writeLine($text= '') {
    $this->stream->write(iconv(\xp::ENCODING, $this->charset, $text).$this->newLine);
  }
}
