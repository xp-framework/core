<?php namespace io\streams;

/**
 * Writes text from to underlying output stream, encoding to the
 * given character set.
 *
 * @ext   iconv
 * @test  io.unittest.TextWriterTest
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
    parent::__construct($arg);
    $this->charset= (string)$charset;
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
   * Writes text
   *
   * @param  string $text
   * @return int
   */
  protected function write0($text) {
    return $this->stream->write(iconv(\xp::ENCODING, $this->charset, $text));
  }
}
