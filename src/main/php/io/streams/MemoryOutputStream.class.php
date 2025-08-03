<?php namespace io\streams;

use io\IOException;
use lang\Value;
use util\Comparison;

/**
 * OuputStream writes to memory, which can be retrieved via `bytes()`
 *
 * @test  io.unittest.MemoryOutputStreamTest
 */
class MemoryOutputStream implements OutputStream, Seekable, Truncation, Value {
  use Comparison;

  protected $pos, $bytes;

  /** @param string $bytes */
  public function __construct($bytes= '') {
    $this->bytes= (string)$bytes;
    $this->pos= strlen($this->bytes);
  }

  /**
   * Write a string
   *
   * @param  var $arg
   * @return void
   */
  public function write($arg) {
    $l= strlen($arg);
    $length= strlen($this->bytes);
    if ($this->pos < $length) {
      $this->bytes= substr_replace($this->bytes, $arg, $this->pos, $l);
    } else if ($this->pos > $length) {
      $this->bytes.= str_repeat("\x00", $this->pos - $length).$arg;
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
   * @param  int $offset
   * @param  int $whence default SEEK_SET (one of SEEK_[SET|CUR|END])
   * @throws io.IOException
   * @return void
   */
  public function seek($offset, $whence= SEEK_SET) {
    switch ($whence) {
      case SEEK_SET: $this->pos= $offset; break;
      case SEEK_CUR: $this->pos+= $offset; break;
      case SEEK_END: $this->pos= strlen($this->bytes) + $offset; break;
      default: throw new IOException('Unexpected whence '.$whence);
    }

    // Ensure we cannot seek *before* start
    if ($this->pos < 0) {
      $this->pos= 0;
      throw new IOException('Seek error, position '.$offset.', whence: '.$whence);
    }
  }

  /** @return int */
  public function tell() { return $this->pos; }

  /** @return int */
  public function size() { return strlen($this->bytes); }

  /** @return string */
  public function bytes() { return $this->bytes; }

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
