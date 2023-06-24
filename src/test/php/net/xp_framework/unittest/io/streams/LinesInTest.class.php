<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\{InputStream, LinesIn, MemoryInputStream, TextReader};
use io\{File, IOException};
use lang\IllegalArgumentException;
use unittest\{Assert, Expect, Test, Values};

class LinesInTest {

  /** @return iterable */
  private function iso88591Input() {
    yield [new LinesIn("\xfc", "iso-8859-1")];
    yield [new LinesIn(new MemoryInputStream("\xfc"), "iso-8859-1")];
    yield [new LinesIn(new TextReader(new MemoryInputStream("\xfc"), "iso-8859-1"))];
  }

  #[Test]
  public function can_create_with_string() {
    new LinesIn('');
  }

  #[Test]
  public function can_create_with_stream() {
    new LinesIn(new MemoryInputStream(''));
  }

  #[Test]
  public function can_create_with_channel() {
    new LinesIn(new File(__FILE__));
  }

  #[Test]
  public function can_create_with_reader() {
    new LinesIn(new TextReader(new MemoryInputStream(''), 'utf-8'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function raises_exception_for_incorrect_constructor_argument() {
    new LinesIn(null);
  }

  #[Test, Values([["Line 1", [1 => 'Line 1']], ["Line 1\n", [1 => 'Line 1']], ["Line 1\nLine 2", [1 => 'Line 1', 2 => 'Line 2']], ["Line 1\nLine 2\n", [1 => 'Line 1', 2 => 'Line 2']], ["Line 1\n\nLine 3\n", [1 => 'Line 1', 2 => '', 3 => 'Line 3']], ["Line 1\n\n\nLine 4\n", [1 => 'Line 1', 2 => '', 3 => '', 4 => 'Line 4']]])]
  public function iterating_lines($input, $lines) {
    Assert::equals($lines, iterator_to_array(new LinesIn($input)));
  }

  #[Test]
  public function iterating_empty_input_returns_empty_array() {
    Assert::equals([], iterator_to_array(new LinesIn('')));
  }

  #[Test]
  public function can_iterate_twice_on_seekable() {
    $fixture= new LinesIn("A\nB");
    Assert::equals(
      [[1 => 'A', 2 => 'B'], [1 => 'A', 2 => 'B']],
      [iterator_to_array($fixture), iterator_to_array($fixture)]
    );
  }

  #[Test, Values('iso88591Input')]
  public function input_is_encoded_to_utf8($arg) {
    Assert::equals([1 => 'ü'], iterator_to_array($arg));
  }

  #[Test]
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

    Assert::equals([1 => 'A', 2 => 'B'], iterator_to_array($fixture));
    try {
      iterator_to_array($fixture);
      $this->fail('No exception raised', null, 'io.IOException');
    } catch (IOException $expected) {
      // OK
    }
  }
}