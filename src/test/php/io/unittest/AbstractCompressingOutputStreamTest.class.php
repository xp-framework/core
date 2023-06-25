<?php namespace io\unittest;

use io\streams\{MemoryOutputStream, OutputStream};
use lang\IllegalArgumentException;
use test\{Assert, Expect, Test};
use util\Bytes;

abstract class AbstractCompressingOutputStreamTest {

  /**
   * Get stream
   *
   * @param  io.streams.OutputStream $wrapped
   * @param  int $level
   * @return io.streams.OutputStream
   */
  protected abstract function newStream(OutputStream $wrapped, $level);

  /**
   * Compress data
   *
   * @param  string $in
   * @param  int $level
   * @return string
   */
  protected abstract function compress($in, $level);

  /**
   * Asserts compressed data equals. Used util.Bytes objects in
   * comparison to prevent binary data from appearing in assertion 
   * failure message.
   *
   * @param  string $expected
   * @param  string $actual
   * @throws unittest.AssertionFailedError
   */
  protected function assertCompressedDataEquals($expected, $actual) {
    Assert::equals(new Bytes($expected), new Bytes($actual));
  }

  #[Test]
  public function singleWrite() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 6);
    $compressor->write('Hello');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello', 6), $out->bytes());
  }

  #[Test]
  public function multipeWrites() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 6);
    $compressor->write('Hello');
    $compressor->write(' ');
    $compressor->write('World');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello World', 6), $out->bytes());
  }

  #[Test]
  public function highestLevel() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 9);
    $compressor->write('Hello');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello', 9), $out->bytes());
  }

  #[Test]
  public function lowestLevel() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 1);
    $compressor->write('Hello');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello', 1), $out->bytes());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function levelTooHigh() {
    $this->newStream(new MemoryOutputStream() , 10);
  }
 
  #[Test, Expect(IllegalArgumentException::class)]
  public function levelTooLow() {
    $this->newStream(new MemoryOutputStream(), -1);
  }

  #[Test]
  public function closingRightAfterCreation() {
    $compressor= $this->newStream(new MemoryOutputStream(), 1);
    $compressor->close();
  }

  #[Test]
  public function closingTwice() {
    $compressor= $this->newStream(new MemoryOutputStream(), 1);
    $compressor->close();
    $compressor->close();
  }
}