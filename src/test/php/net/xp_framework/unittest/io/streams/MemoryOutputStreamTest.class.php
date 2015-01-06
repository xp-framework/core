<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\MemoryOutputStream;

/**
 * Unit tests for streams API
 *
 * @see   xp://io.streams.OutputStream
 * @see   xp://lang.Closeable#close
 */
class MemoryOutputStreamTest extends \unittest\TestCase {
  private $out;

  /**
   * Setup method. Creates the fixture.
   */
  public function setUp() {
    $this->out= new MemoryOutputStream();
  }

  #[@test]
  public function writing_a_string() {
    $this->out->write('Hello');
    $this->assertEquals('Hello', $this->out->getBytes());
  }

  #[@test]
  public function writing_a_number() {
    $this->out->write(5);
    $this->assertEquals('5', $this->out->getBytes());
  }

  #[@test]
  public function closingTwice() {
    $this->out->close();
    $this->out->close();
  }
}
