<?php namespace io\streams;

use io\IOException;
use io\Channel;
use lang\FormatException;
use lang\IllegalArgumentException;

/**
 * Reads text from an underlying input stream, converting it from the
 * given character set to our internal encoding (which is iso-8859-1).
 *
 * @test  xp://net.xp_framework.unittest.io.streams.TextReaderTest
 * @ext   iconv
 */
class TextReader extends Reader {
  private $buf= '';
  private $bom;
  private $charset;
  private $beginning;

  private $cl= 1, $of= 0;

  /**
   * Constructor. Creates a new TextReader on an underlying input
   * stream with a given charset.
   *
   * @param   io.streams.InputStream|io.Channel|string $arg The input source
   * @param   string $charset the charset the stream is encoded in or NULL to trigger autodetection by BOM
   * @throws  lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= null) {
    if ($arg instanceof InputStream) {
      parent::__construct($arg);
    } else if ($arg instanceof Channel) {
      parent::__construct($arg->in());
    } else if (is_string($arg)) {
      parent::__construct(new MemoryInputStream($arg));
    } else {
      throw new IllegalArgumentException('Given argument is neither an input stream, a channel nor a string: '.\xp::typeOf($arg));
    }

    $this->beginning= true;
    switch ($this->charset= strtolower($charset ?: $this->detectCharset())) {
      case 'utf-16le': $this->cl= 2; $this->of= 0; break;
      case 'utf-16be': $this->cl= 2; $this->of= 1; break;
      case 'utf-32le': $this->cl= 4; $this->of= 0; break;
      case 'utf-32be': $this->cl= 4; $this->of= 3; break;
    }
  }

  /**
   * Returns the character set used
   *
   * @return  string
   */
  public function charset() { return $this->charset; }

  /**
   * Returns whether we're at the beginning of the stream
   *
   * @return  bool
   */
  public function atBeginning() { return $this->beginning; }

  /**
   * Reset to BOM 
   *
   * @throws  io.IOException in case the underlying stream does not support seeking
   */
  public function reset() {
    if ($this->stream instanceof Seekable) {
      $this->stream->seek($this->bom, SEEK_SET);
      $this->beginning= true;
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
    $this->beginning= false;

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
      $message= key(@\xp::$errors[__FILE__][__LINE__ - 3]);
      \xp::gc(__FILE__);
      throw new FormatException($message);
    } else if ('' === $bytes) {
      $this->buf= null;
      return null;
    } else {
      return iconv($this->charset, \xp::ENCODING, $bytes);
    }
  }

  /**
   * Read an entire line
   *
   * @return  string NULL when end of data is reached
   */
  public function readLine() {
    if (null === $this->buf) return null;

    $this->beginning= false;

    // Search for \r or \n, whatever comes first. If neither of both can be
    // found, read another chunk from the underlying stream.
    read:
    $p= strcspn($this->buf, "\r\n");
    $l= strlen($this->buf);
    if ($p >= $l - $this->cl) {
      $chunk= $this->stream->read();
      if ('' === $chunk) {
        if ('' === $this->buf) return null;
        $bytes= $p === $l ? $this->buf : substr($this->buf, 0, $p - $this->of);
        $this->buf= null;
      } else {
        $this->buf.= $chunk;
        goto read;
      }
    } else {
      $o= ("\r" === $this->buf{$p} && "\n" === $this->buf{$p + $this->cl}) ? $this->cl * 2 : $this->cl;
      $p-= $this->of;
      $bytes= substr($this->buf, 0, $p);
      $this->buf= substr($this->buf, $p + $o);
    }

    $line= iconv($this->charset, \xp::ENCODING, $bytes);
    if (false === $line) {
      $message= key(@\xp::$errors[__FILE__][__LINE__ - 2]);
      \xp::gc(__FILE__);
      throw new FormatException($message);
    }
    return $line;
  }

  /**
   * Reads all lines in this reader
   *
   * @return io.streams.LinesIn
   */
  public function lines() { return new LinesIn($this, $this->charset, true); }

  /**
   * Reads the lines starting at the current position
   *
   * @return io.streams.LinesIn
   */
  public function readLines() { return new LinesIn($this, $this->charset, false); }
}
