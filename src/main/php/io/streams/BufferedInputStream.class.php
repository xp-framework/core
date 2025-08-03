<?php namespace io\streams;

use lang\Value;
use util\Comparison;

/**
 * Buffered InputStream
 *
 * @test  io.unittest.BufferedInputStreamTest
 */
class BufferedInputStream implements InputStream, Value {
  use Comparison;

  protected $in, $size;
  protected $buf= '';

  /**
   * Constructor
   *
   * @param  io.streams.InputStream $in
   * @param  int $size
   */
  public function __construct($in, $size= 512) {
    $this->in= $in;
    $this->size= $size;
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    while (strlen($this->buf) < $limit) {
      if (null === ($read= $this->in->read($this->size))) break;
      $this->buf.= $read;
    }
    $chunk= substr($this->buf, 0, $limit);
    $this->buf= substr($this->buf, $limit);
    return $chunk;
  }

  /**
   * Push back
   *
   * @param   string bytes
   */
  public function pushBack($bytes) {
    $this->buf= $bytes.$this->buf;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   * @return int
   */
  public function available() {
    return strlen($this->buf) ?: $this->in->available();
  }

  /**
   * Close this buffer
   *
   * @return void
   */
  public function close() {
    // NOOP
  }

  /** @return string */
  public function toString() {
    return nameof($this).'(buffered '.strlen($this->buf).' of '.$this->size.')';
  }
}