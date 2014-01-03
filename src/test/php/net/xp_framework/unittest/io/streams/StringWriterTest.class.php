<?php namespace net\xp_framework\unittest\io\streams;

use unittest\TestCase;
use io\streams\StringWriter;
use io\streams\MemoryOutputStream;
use lang\types\String;

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

  /**
   * Returns values to be written
   *
   * @return var[] args
   */
  protected function values() {
    return array(
      array('1', 1), array('0', 0), array('-1', -1),
      array('1', 1.0),
      array('true', true), array('false', false),
      array('Test', 'Test'), array('', ''),
      array("[\n]", array()), array("[\n  0 => 1\n  1 => 2\n  2 => 3\n]", [1, 2, 3]),
      array("[\n  a => \"b\"\n  c => \"d\"\n]", array('a' => 'b', 'c' => 'd')),
      array('Test', new String('Test'))
    );
  }

  #[@test, @values('values')]
  public function write($expected, $value) {
    $this->assertWritten($expected, function($fixture) use($value) {
      $fixture->write($value);
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
  public function writeLinef() {
    $this->assertWritten("Some string: test, some int: 6100\n", function($fixture) {
      $fixture->writeLinef('Some string: %s, some int: %d', 'test', 6100);
    });
  }
}
