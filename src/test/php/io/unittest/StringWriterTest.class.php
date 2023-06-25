<?php namespace io\unittest;

use io\streams\{MemoryOutputStream, StringWriter};
use lang\Value;
use test\verify\Runtime;
use test\{Assert, Test, Values};
use util\Comparison;

class StringWriterTest {

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
      Assert::equals($bytes, $out->bytes());
    });
  }

  /**
   * Returns values to be written
   *
   * @return var[] args
   */
  protected function values() {
    return [
      ['null', null],
      ['1', 1], ['0', 0], ['-1', -1],
      ['1', 1.0], ['0', 0.0], ['-1', -1.0], ['0.5', 0.5],
      ['true', true], ['false', false],
      ['Test', 'Test'], ['', ''],
      ["[]", []], ["[1, 2, 3]", [1, 2, 3]],
      ["[\n  a => \"b\"\n  c => \"d\"\n]", ['a' => 'b', 'c' => 'd']],
      ['Test', new class() implements Value {
        use Comparison;
        public function toString() { return 'Test'; }
      }]
    ];
  }

  #[Test, Values(from: 'values')]
  public function write($expected, $value) {
    $this->assertWritten($expected, function($fixture) use($value) {
      $fixture->write($value);
    });
  }

  #[Test]
  public function write_supports_var_args() {
    $this->assertWritten('1two3four', function($fixture) {
      $fixture->write(1, 'two', 3.0, new class() implements Value {
        use Comparison;
        public function toString() { return 'four'; }
      });
    });
  }

  #[Test]
  public function writef() {
    $this->assertWritten('Some string: test, some int: 6100', function($fixture) {
      $fixture->writef('Some string: %s, some int: %d', 'test', 6100);
    });
  }

  #[Test, Values(from: 'values')]
  public function writeLine($expected, $value) {
    $this->assertWritten($expected."\n", function($fixture) use($value) {
      $fixture->writeLine($value);
    });
  }

  #[Test]
  public function writeLine_supports_var_args() {
    $this->assertWritten("1two3four\n", function($fixture) {
      $fixture->writeLine(1, 'two', 3.0, new class() implements Value {
        use Comparison;
        public function toString() { return 'four'; }
      });
    });
  }

  #[Test]
  public function writeLinef() {
    $this->assertWritten("Some string: test, some int: 6100\n", function($fixture) {
      $fixture->writeLinef('Some string: %s, some int: %d', 'test', 6100);
    });
  }
}