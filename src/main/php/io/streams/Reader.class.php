<?php namespace io\streams;

use io\IOException;
use lang\{Closeable, Value};
use util\Objects;

/**
 * Serves as an abstract base class for all other readers. A reader
 * returns characters it reads from the underlying InputStream
 * implementation (which works with bytes - for single-byte character
 * sets, there is no difference, obviously).
 */
abstract class Reader implements InputStreamReader, Closeable, Value {
  protected $stream= null;
  protected $buf= '';
  protected $beginning= true;
  protected $start= 0;

  /**
   * Creates a new Reader from an InputStream.
   *
   * @param io.streams.InputStream $stream
   */
  public function __construct(InputStream $stream) {
    $this->stream= $stream;
  }

  /**
   * Returns whether we're at the beginning of the stream
   *
   * @return  bool
   */
  public function atBeginning() { return $this->beginning; }

  /**
   * Returns the underlying stream
   *
   * @deprecated Use stream() instead
   * @return  io.streams.InputStream stream
   */
  public function getStream() { return $this->stream; }

  /**
   * Returns the underlying stream
   *
   * @return  io.streams.InputStream stream
   */
  public function stream() { return $this->stream; }

  /**
   * Return underlying output stream
   *
   * @param  io.streams.InputStream stream
   * @return void
   */
  public function redirect(InputStream $stream) {
    $this->stream= $stream;
    $this->beginning= true;
    $this->buf= '';
  }

  /**
   * Reset to start.
   *
   * @throws  io.IOException in case the underlying stream does not support seeking
   */
  public function reset() {
    if ($this->stream instanceof Seekable) {
      $this->stream->seek($this->start, SEEK_SET);
      $this->beginning= true;
      $this->buf= '';
    } else {
      throw new IOException('Underlying stream does not support seeking');
    }
  }

  /**
   * Reads a given number of bytes
   *
   * @param  int $size
   * @return string
   */
  protected function read0($size) {
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
   * Reads all lines in this reader
   *
   * @return io.streams.LinesIn
   */
  public function lines() { return new LinesIn($this, null, true); }

  /**
   * Reads the lines starting at the current position
   *
   * @return io.streams.LinesIn
   */
  public function readLines() { return new LinesIn($this, null, false); }

  /**
   * Closes this reader (and the underlying stream)
   *
   * @return void
   */
  public function close() {
    $this->stream->close();
  }

  /** @return void */
  public function __destruct() {
    $this->close();
  }

  /** @return string */
  public function toString() {
    return nameof($this)."@{\n  ".$this->stream->toString()."\n}";
  }

  /** @return string */
  public function hashCode() {
    return Objects::hashOf((array)$this);
  }

  /**
   * Compares this reader to a given value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->stream, $value->stream) : 1;
  }
}
