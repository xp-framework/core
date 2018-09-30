<?php namespace io\streams;

/**
 * OuputStream writes to memory, which can be retrieved via `bytes()`
 *
 * @test  xp://net.xp_framework.unittest.io.streams.MemoryOutputStreamTest
 */
class MemoryOutputStream implements OutputStream, Seekable, Truncation {
  protected $pos, $bytes;

  /** @param string $bytes */
  public function __construct($bytes= '') {
    $this->bytes= $bytes;
    $this->pos= strlen($bytes);
  }

  /**
   * Write a string
   *
   * @param  var $arg
   */
  public function write($arg) {
    $l= strlen($arg);
    if ($this->pos < strlen($this->bytes)) {
      $this->bytes= substr_replace($this->bytes, $arg, $this->pos, $l);
    } else {
      $this->bytes.= $arg;
    }
    $this->pos+= $l;
  }

  /**
   * Truncate this buffer to a given new size.
   *
   * @param  int $size
   * @return void
   */
  public function truncate($size) {
    $length= strlen($this->bytes);
    if ($size > $length) {
      $this->bytes.= str_repeat("\x00", $size - $length);
    } else if ($size < $length) {
      $this->bytes= substr($this->bytes, 0, $size);
    }
  }

  /**
   * Flush this buffer. A NOOP for this implementation.
   *
   * @return void
   */
  public function flush() { }

  /**
   * Seek to a given offset
   *
   * @param   int $offset
   * @param   int $whence default SEEK_SET (one of SEEK_[SET|CUR|END])
   * @throws  io.IOException in case of error
   */
  public function seek($offset, $whence= SEEK_SET) {
    switch ($whence) {
      case SEEK_SET: $this->pos= $offset; break;
      case SEEK_CUR: $this->pos+= $offset; break;
      case SEEK_END: $this->pos= strlen($this->bytes) + $offset; break;
    }
  }

  /** @return int */
  public function tell() { return $this->pos; }

  /** @return string */
  public function bytes() { return $this->bytes; }

  /**
   * Retrieve stored bytes
   *
   * @deprecated Use bytes() instead!
   * @return  string
   */
  public function getBytes() { 
    return $this->bytes;
  }

  /**
   * Close this output stream.
   *
   * @return void
   */
  public function close() { }

  /** @return string */
  public function toString() {
    return nameof($this).'(@'.$this->pos.' of '.strlen($this->bytes).' bytes)';
  }
}
