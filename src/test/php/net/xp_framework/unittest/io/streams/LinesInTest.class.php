<?php namespace net\xp_framework\unittest\io\streams;

use io\{File, IOException};
use io\streams\{TextReader, LinesIn, InputStream, MemoryInputStream};
use lang\IllegalArgumentException;

class LinesInTest extends \unittest\TestCase {

  #[@test]
  public function can_create_with_string() {
    new LinesIn('');
  }

  #[@test]
  public function can_create_with_stream() {
    new LinesIn(new MemoryInputStream(''));
  }

  #[@test]
  public function can_create_with_channel() {
    new LinesIn(new File(__FILE__));
  }

  #[@test]
  public function can_create_with_reader() {
    new LinesIn(new TextReader(new MemoryInputStream(''), 'utf-8'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function raises_exception_for_incorrect_constructor_argument() {
    new LinesIn(null);
  }

  #[@test, @values([
  #  ["Line 1", [1 => 'Line 1']],
  #  ["Line 1\n", [1 => 'Line 1']],
  #  ["Line 1\nLine 2", [1 => 'Line 1', 2 => 'Line 2']],
  #  ["Line 1\nLine 2\n", [1 => 'Line 1', 2 => 'Line 2']],
  #  ["Line 1\n\nLine 3\n", [1 => 'Line 1', 2 => '', 3 => 'Line 3']],
  #  ["Line 1\n\n\nLine 4\n", [1 => 'Line 1', 2 => '', 3 => '', 4 => 'Line 4']]
  #])]
  public function iterating_lines($input, $lines) {
    $this->assertEquals($lines, iterator_to_array(new LinesIn($input)));
  }

  #[@test]
  public function iterating_empty_input_returns_empty_array() {
    $this->assertEquals([], iterator_to_array(new LinesIn('')));
  }

  #[@test]
  public function can_iterate_twice_on_seekable() {
    $fixture= new LinesIn("A\nB");
    $this->assertEquals(
      [[1 => 'A', 2 => 'B'], [1 => 'A', 2 => 'B']],
      [iterator_to_array($fixture), iterator_to_array($fixture)]
    );
  }

  #[@test, @values([
  #  [new LinesIn("\xFC", "iso-8859-1")],
  #  [new LinesIn(new MemoryInputStream("\xFC"), "iso-8859-1")],
  #  [new LinesIn(new TextReader(new MemoryInputStream("\xFC"), "iso-8859-1"))]
  #])]
  public function input_is_encoded_to_utf8($arg) {
    $this->assertEquals([1 => 'ü'], iterator_to_array($arg));
  }

  #[@test]
  public function can_only_iterate_unseekable_once() {
    $fixture= new LinesIn(new class() implements InputStream {
      public $bytes = "A\nB\n";
      public $offset = 0;
      public function read($length= 8192) {
        $chunk= substr($this->bytes, $this->offset, $length);
        $this->offset+= strlen($chunk);
        return $chunk;
      }
      public function available() {
        return strlen($this->bytes) - $this->offset;
      }
      public function close() { }
    });

    $this->assertEquals([1 => 'A', 2 => 'B'], iterator_to_array($fixture));
    try {
      iterator_to_array($fixture);
      $this->fail('No exception raised', null, 'io.IOException');
    } catch (IOException $expected) {
      // OK
    }
  }
}