<?php namespace net\xp_framework\unittest\io\streams;

use unittest\TestCase;
use io\streams\StringWriter;
use io\streams\MemoryOutputStream;
use lang\types\String;

/**
 * Test StringWriter
 *
 * @see  xp://io.streams.StringWriter
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

  /**
   * Returns values to be written
   *
   * @return var[] args
   */
  protected function values() {
    return array(
      array('1', 1), array('0', 0), array('-1', -1),
      array('1', 1.0), array('0', 0.0), array('-1', -1.0), array('0.5', 0.5),
      array('true', true), array('false', false),
      array('Test', 'Test'), array('', ''),
      array("[\n]", []), array("[1, 2, 3]", [1, 2, 3]),
      array("[\n  a => \"b\"\n  c => \"d\"\n]", array('a' => 'b', 'c' => 'd')),
      array('Test', new String('Test')),
      array('Test', newinstance('lang.Object', [], array('toString' => function() { return 'Test'; } )))
    );
  }

  #[@test, @values('values')]
  public function write($expected, $value) {
    $this->assertWritten($expected, function($fixture) use($value) {
      $fixture->write($value);
    });
  }

  #[@test]
  public function write_supports_var_args() {
    $this->assertWritten('1two3four', function($fixture) {
      $fixture->write(1, 'two', 3.0, new String('four'));
    });
  }

  #[@test]
  public function writef() {
    $this->assertWritten('Some string: test, some int: 6100', function($fixture) {
      $fixture->writef('Some string: %s, some int: %d', 'test', 6100);
    });
  }

  #[@test, @values('values')]
  public function writeLine($expected, $value) {
    $this->assertWritten($expected."\n", function($fixture) use($value) {
      $fixture->writeLine($value);
    });
  }

  #[@test]
  public function writeLine_supports_var_args() {
    $this->assertWritten("1two3four\n", function($fixture) {
      $fixture->writeLine(1, 'two', 3.0, new String('four'));
    });
  }

  #[@test]
  public function writeLinef() {
    $this->assertWritten("Some string: test, some int: 6100\n", function($fixture) {
      $fixture->writeLinef('Some string: %s, some int: %d', 'test', 6100);
    });
  }
}
