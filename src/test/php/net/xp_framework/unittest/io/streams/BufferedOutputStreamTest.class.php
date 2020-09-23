<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\{BufferedOutputStream, MemoryOutputStream};
use unittest\Test;

/**
 * Unit tests for streams API
 *
 * @see   xp://io.streams.OutputStream
 */
class BufferedOutputStreamTest extends \unittest\TestCase {
  protected 
    $out= null,
    $mem= null;
  
  /**
   * Setup method. Creates the fixture, a BufferedOutputStream with
   * a buffer size of 10 characters.
   */
  public function setUp() {
    $this->mem= new MemoryOutputStream();
    $this->out= new BufferedOutputStream($this->mem, 10);
  }

  #[Test]
  public function doNotFillBuffer() {
    $this->out->write('Hello');
    $this->assertEquals('', $this->mem->bytes());
  }

  #[Test]
  public function fillBuffer() {
    $this->out->write(str_repeat('*', 10));
    $this->assertEquals('', $this->mem->bytes());
  }

  #[Test]
  public function overFlowBuffer() {
    $this->out->write('A long string that will fill the buffer');
    $this->assertEquals('A long string that will fill the buffer', $this->mem->bytes());
  }

  #[Test]
  public function flushed() {
    $this->out->write('Hello');
    $this->out->flush();
    $this->assertEquals('Hello', $this->mem->bytes());
  }

  #[Test]
  public function flushedOnClose() {
    $this->out->write('Hello');
    $this->out->close();
    $this->assertEquals('Hello', $this->mem->bytes());
  }

  #[Test]
  public function flushedOnDestruction() {
    $this->out->write('Hello');
    unset($this->out);
    $this->assertEquals('Hello', $this->mem->bytes());
  }
}