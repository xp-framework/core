<?php namespace io\streams;

use io\IOException;

/**
 * Reads text from an underlying input stream, converting it from the
 * given character set to our internal encoding (which is iso-8859-1).
 *
 * @test    xp://net.xp_framework.unittest.io.streams.TextReaderTest
 * @ext     iconv
 */
class TextReader extends Reader {
  protected $buf = '';
  protected $bom = 0;
  protected $charset = null;

  /**
   * Constructor. Creates a new TextReader on an underlying input
   * stream with a given charset.
   *
   * @param   io.streams.InputStream $stream
   * @param   string $charset the charset the stream is encoded in or NULL to trigger autodetection by BOM
   */
  public function __construct(InputStream $stream, $charset= null) {
    parent::__construct($stream);
    $this->charset= $charset ?: $this->detectCharset();
  }

  /**
   * Returns the character set used
   *
   * @return  string
   */
  public function charset() {
    return $this->charset;
  }

  /**
   * Reset to BOM 
   *
   * @throws  io.IOException in case the underlying stream does not support seeking
   */
  public function reset() {
    if ($this->stream instanceof Seekable) {
      $this->stream->seek($this->bom, SEEK_SET);
      $this->buf= '';
    } else {
      throw new IOException('Underlying stream does not support seeking');
    }
  }

  /**
   * Detect charset of stream
   *
   * @see     http://de.wikipedia.org/wiki/Byte_Order_Mark
   * @see     http://unicode.org/faq/utf_bom.html
   * @return  string
   */
  protected function detectCharset() {
    $c= $this->stream->read(2);

    // Check for UTF-16 (BE)
    if ("\376\377" === $c) {
      $this->bom= 2;
      return 'utf-16be';
    }

    // Check for UTF-16 (LE)
    if ("\377\376" === $c) {
      $this->bom= 2;
      return 'utf-16le';
    }

    // Check for UTF-8 BOM
    if ("\357\273" === $c && "\357\273\277" === ($c.= $this->stream->read(1))) {
      $this->bom= 3;
      return 'utf-8';
    }

    // Fall back to ISO-8859-1
    $this->buf= (string)$c;
    $this->bom= 0;
    return 'iso-8859-1';
  }

  /**
   * Reads a given number of bytes
   *
   * @param  int $size
   * @return string
   */
  private function read0($size) {
    $len= $size - strlen($this->buf);
    if ($len > 0) {
      $bytes= $this->buf.$this->stream->read($len);
      $this->buf= '';
    } else {
      $bytes= substr($this->buf, 0, $size);
      $this->buf= substr($this->buf, $size);
    }
    return $bytes;
  }

  /**
   * Read a number of characters
   *
   * @param   int size default 8192
   * @return  string NULL when end of data is reached
   */
  public function read($size= 8192) {
    if (0 === $size) return '';

    // fread() will always work with bytes, so reading may actually read part of
    // an incomplete multi-byte sequence. In this case, iconv_strlen() will raise
    // a warning, and return FALSE (or 0, for the "less than" operator), causing
    // the loop to read more. Maybe there's a more elegant way to do this?
    $l= 0;
    $bytes= '';
    do {
      if ('' === ($chunk= $this->read0($size - $l))) break;
      $bytes.= $chunk;
    } while (($l= @iconv_strlen($bytes, $this->charset)) < $size);

    if (false === $l) {
      $message= key(\xp::$errors[__FILE__][__LINE__ - 3]);
      \xp::gc(__FILE__);
      throw new \lang\FormatException($message);
    }

    return '' === $bytes ? null : iconv($this->charset, \xp::ENCODING, $bytes);
  }
  
  /**
   * Read an entire line
   *
   * @return  string NULL when end of data is reached
   */
  public function readLine() {
    if (null === ($c= $this->read(1))) return null;
    $line= '';
    do {
      if ("\r" === $c) {
        $n= $this->read(1);
        if ("\n" !== $n) $this->buf= $n.$this->buf;
        return $line;
      } else if ("\n" === $c) {
        return $line;
      }
      $line.= $c;
    } while (null !== ($c= $this->read(1)));
    return $line;
  }
}
