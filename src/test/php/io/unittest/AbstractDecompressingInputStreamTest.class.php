<?php namespace io\unittest;

use io\streams\{InputStream, MemoryInputStream};
use test\{Assert, PrerequisitesNotMetError, Test};
use util\Bytes;

abstract class AbstractDecompressingInputStreamTest {

  /**
   * Get filter we depend on
   *
   * @return  string
   */
  protected abstract function filter();

  /**
   * Get stream
   *
   * @param   io.streams.InputStream wrapped
   * @return  io.streams.InputStream
   */
  protected abstract function newStream(InputStream $wrapped);

  /**
   * Compress data
   *
   * @param   string in
   * @return  int level
   * @return  string
   */
  protected abstract function compress($in, $level);

  /**
   * Setup method. Ensure filter we depend on is available
   */
  #[Before]
  public function setUp() {
    $depend= $this->filter();
    if (!in_array($depend, stream_get_filters())) {
      throw new PrerequisitesNotMetError(ucfirst($depend).' stream filter not available', null, [$depend]);
    }
  }

  #[Test]
  public function single_read() {
    $in= new MemoryInputStream($this->compress('Hello', 6));
    $decompressor= $this->newStream($in);
    $chunk= $decompressor->read();
    $decompressor->close();
    Assert::equals('Hello', $chunk);
  }

  #[Test]
  public function multiple_reads() {
    $in= new MemoryInputStream($this->compress('Hello World', 6));
    $decompressor= $this->newStream($in);
    $chunk1= $decompressor->read(5);
    $chunk2= $decompressor->read(1);
    $chunk3= $decompressor->read(5);
    $decompressor->close();
    Assert::equals('Hello', $chunk1);
    Assert::equals(' ', $chunk2);
    Assert::equals('World', $chunk3);
  }

  #[Test]
  public function highest_level() {
    $in= new MemoryInputStream($this->compress('Hello', 9));
    $decompressor= $this->newStream($in);
    $chunk= $decompressor->read();
    $decompressor->close();
    Assert::equals('Hello', $chunk);
  }

  #[Test]
  public function lowest_level() {
    $in= new MemoryInputStream($this->compress('Hello', 1));
    $decompressor= $this->newStream($in);
    $chunk= $decompressor->read();
    $decompressor->close();
    Assert::equals('Hello', $chunk);
  }

  #[Test]
  public function closing_right_after_creation() {
    $decompressor= $this->newStream(new MemoryInputStream($this->compress('Hello', 1)));
    $decompressor->close();
  }

  #[Test]
  public function closing_twice_has_no_effect() {
    $decompressor= $this->newStream(new MemoryInputStream($this->compress('Hello', 1)));
    $decompressor->close();
    $decompressor->close();
  }
}