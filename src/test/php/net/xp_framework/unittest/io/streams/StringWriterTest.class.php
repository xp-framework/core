<?php namespace net\xp_framework\unittest\io\streams;

use unittest\TestCase;
use io\streams\StringWriter;
use io\streams\MemoryOutputStream;

/**
 * Test StringReader
 *
 * @see  xp://io.streams.StringReader
 */
class StringWriterTest extends TestCase {

  /**
   * Assert a given string has been written to the fixture after 
   * invoking a specified closure.
   *
   * @param  string $bytes
   * @param  var $closure
   * @throws unittest.AssertionFailedError
   */
  protected function assertWritten($bytes, $closure) {
    with (new MemoryOutputStream(), function($out) use($bytes, $closure) {
      $fixture= new StringWriter($out);
      $closure($fixture);
      $this->assertEquals($bytes, $out->getBytes());
    });
  }

  #[@test]
  public function write() {
    $this->assertWritten('This is a test', function($fixture) {
      $fixture->write('This is a test');
    });
  }

  #[@test]
  public function writef() {
    $this->assertWritten('Some string: test, some int: 6100', function($fixture) {
      $fixture->writef('Some string: %s, some int: %d', 'test', 6100);
    });
  }

  #[@test]
  public function writeLine() {
    $this->assertWritten("This is a test\n", function($fixture) {
      $fixture->writeLine('This is a test');
    });
  }

  #[@test]
  public function writeLinef() {
    $this->assertWritten("Some string: test, some int: 6100\n", function($fixture) {
      $fixture->writeLinef('Some string: %s, some int: %d', 'test', 6100);
    });
  }
}
