<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\MemoryInputStream;
use unittest\{Test, TestCase};

class MemoryInputStreamTest extends TestCase {
  const BUFFER= 'Hello World, how are you doing?';

  protected $in= null;

  /**
   * Setup method. Creates the fixture.
   *
   */
  public function setUp() {
    $this->in= new MemoryInputStream(self::BUFFER);
  }

  /**
   * Test reading all
   *
   */
  #[Test]
  public function readAll() {
    $this->assertEquals(self::BUFFER, $this->in->read(strlen(self::BUFFER)));
    $this->assertEquals(0, $this->in->available());
  }

  /**
   * Test reading a five byte chunk
   *
   */
  #[Test]
  public function readChunk() {
    $this->assertEquals('Hello', $this->in->read(5));
    $this->assertEquals(strlen(self::BUFFER)- 5, $this->in->available());
  }
  
  /**
   * Test closing a stream twice has no effect.
   *
   * @see   xp://lang.Closeable#close
   */
  #[Test]
  public function closingTwice() {
    $this->in->close();
    $this->in->close();
  }
}