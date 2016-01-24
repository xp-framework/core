<?php namespace io\streams;

/**
 * OuputStream writes to memory, which can be retrieved via `getBytes()`
 *
 * @test  xp://net.xp_framework.unittest.io.streams.MemoryOutputStreamTest
 */
class MemoryOutputStream implements OutputStream, Seekable {
  protected $pos= 0;
  protected $bytes= '';
  
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
   * Flush this buffer. A NOOP for this implementation.
   *
   * @return void
   */
  public function flush() { 
  }

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

  /**
   * Return current offset
   *
   * @return  int
   */
  public function tell() {
    return $this->pos;
  }

  /**
   * Retrieve stored bytes
   *
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
  public function close() {
  }

  /**
   * Destructor.
   */
  public function __destruct() {
    unset($this->bytes);
  }
}
