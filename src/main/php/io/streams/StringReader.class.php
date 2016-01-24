<?php namespace io\streams;

/**
 * A InputStreamReader implementation that reads string values of
 * the given arguments from the underlying input stream.
 *
 * @test  xp://net.xp_framework.unittest.io.streams.StringReaderTest
 */
class StringReader implements InputStreamReader {
  protected
    $in  = null,
    $buf = '';
  
  /**
   * Constructor
   *
   * @param   io.streams.InputStream in
   */
  public function __construct($in) {
    $this->in= $in;
  }
  
  /**
   * Return underlying input stream
   *
   * @return  io.streams.InputStream
   */
  public function getStream() {
    return $this->in;
  }

  /**
   * Set underlying input stream
   *
   * @param   io.streams.InputStream stream
   */
  public function setStream(InputStream $stream) {
    $this->in= $stream;
    $this->buf= '';
  }

  /**
   * Creates a string representation of this writer
   *
   * @return  string
   */
  public function toString() {
    return nameof($this)."@{\n  ".$this->in->toString()."\n}";
  }
  
  /**
   * Read
   *
   * @param   int size
   * @return  string
   */
  protected function read0($size) {
    while (strlen($this->buf) < $size && $this->in->available() > 0) {
      if (null === ($read= $this->in->read($size))) break;
      $this->buf.= $read;
    }

    if (0 === strlen($this->buf)) return null;

    $chunk= substr($this->buf, 0, $size);
    $this->buf= substr($this->buf, $size);
    return $chunk;
  }

  /**
   * Read a number of bytes. Returns NULL if no more data is available.
   *
   * @param   int size default 8192
   * @return  string
   */
  public function read($size= 8192) {
    return $this->read0($size);
  }
  
  /**
   * Read an entire line. Returns NULL if no more lines are available.
   *
   * @return  string
   */
  public function readLine() {
    if (null === ($c= $this->read0(1))) return null;
    $line= '';
    do {
      if ("\r" === $c) {
        $n= $this->read0(1);
        if ("\n" !== $n) $this->buf= $n.$this->buf;
        return $line;
      } else if ("\n" === $c) {
        return $line;
      }
      $line.= $c;
    } while (null !== ($c= $this->read0(1)));
    return $line;
  }
}
