<?php namespace net\xp_framework\unittest\io\streams;

use lang\IllegalArgumentException;
use util\Bytes;
use io\streams\MemoryOutputStream;
use io\streams\OutputStream;
use unittest\PrerequisitesNotMetError;

/**
 * Abstract base class for all compressing output stream tests
 */
abstract class AbstractCompressingOutputStreamTest extends \unittest\TestCase {

  /**
   * Get filter we depend on
   *
   * @return string
   */
  protected abstract function filter();

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
    $this->assertEquals(new Bytes($expected), new Bytes($actual));
  }

  /**
   * Setup method. Ensure filter we depend on is available
   *
   * @return void
   */
  public function setUp() {
    $depend= $this->filter();
    if (!in_array($depend, stream_get_filters())) {
      throw new PrerequisitesNotMetError(ucfirst($depend).' stream filter not available', null, [$depend]);
    }
  }

  #[@test]
  public function singleWrite() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 6);
    $compressor->write('Hello');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello', 6), $out->getBytes());
  }

  #[@test]
  public function multipeWrites() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 6);
    $compressor->write('Hello');
    $compressor->write(' ');
    $compressor->write('World');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello World', 6), $out->getBytes());
  }

  #[@test]
  public function highestLevel() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 9);
    $compressor->write('Hello');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello', 9), $out->getBytes());
  }

  #[@test]
  public function lowestLevel() {
    $out= new MemoryOutputStream();
    $compressor= $this->newStream($out, 1);
    $compressor->write('Hello');
    $compressor->close();
    $this->assertCompressedDataEquals($this->compress('Hello', 1), $out->getBytes());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function levelTooHigh() {
    $this->newStream(new MemoryOutputStream() , 10);
  }
 
  #[@test, @expect(IllegalArgumentException::class)]
  public function levelTooLow() {
    $this->newStream(new MemoryOutputStream(), -1);
  }

  #[@test]
  public function closingRightAfterCreation() {
    $compressor= $this->newStream(new MemoryOutputStream(), 1);
    $compressor->close();
  }

  #[@test]
  public function closingTwice() {
    $compressor= $this->newStream(new MemoryOutputStream(), 1);
    $compressor->close();
    $compressor->close();
  }
}
