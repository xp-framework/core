<?php namespace io\streams;

/**
 * OuputStream writes to memory, which can be retrieved via `getBytes()`
 *
 * @test  xp://net.xp_framework.unittest.io.streams.MemoryOutputStreamTest
 */
class MemoryOutputStream extends \lang\Object implements OutputStream {
  protected $bytes= '';
  
  /**
   * Write a string
   *
   * @param  var $arg
   */
  public function write($arg) { 
    $this->bytes.= $arg;
  }

  /**
   * Flush this buffer. A NOOP for this implementation.
   *
   * @return void
   */
  public function flush() { 
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
