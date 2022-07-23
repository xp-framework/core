<?php namespace io\streams;

use lang\Value;
use util\Comparison;

/**
 * OuputStream that writes to another OutputStream but buffers the
 * results internally. This means not every single byte passed to
 * write() will be written.
 * 
 * @test  net.xp_framework.unittest.io.streams.BufferedOutputStreamTest
 */
class BufferedOutputStream implements OutputStream, Value {
  use Comparison;

  protected $out, $size;
  protected $buf= '';

  /**
   * Constructor
   *
   * @param   io.streams.OutputStream out
   * @param   int size default 512
   */
  public function __construct($out, $size= 512) {
    $this->out= $out;
    $this->size= $size;
  }
  
  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    $this->buf.= $arg;
    strlen($this->buf) > $this->size && $this->flush();
  }

  /**
   * Flush this buffer
   *
   * @return void
   */
  public function flush() { 
    $this->out->write($this->buf);
    $this->buf= '';
  }

  /**
   * Close this buffer. Flushes this buffer and then calls the close()
   * method on the underlying OuputStream.
   *
   * @return void
   */
  public function close() {
    $this->flush();
    $this->out->close();
  }

  /**
   * Destructor.
   */
  public function __destruct() {
    $this->close();
  }

  /** @return string */
  public function toString() {
    return nameof($this).'(buffered '.strlen($this->buf).' of '.$this->size.')';
  }
}
