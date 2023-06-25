<?php namespace io\unittest;

use io\streams\{InputStream, LinesIn, MemoryInputStream, MemoryOutputStream, TextReader};
use io\{Channel, IOException};
use lang\{FormatException, IllegalArgumentException};
use unittest\{Assert, Expect, Test, Values};

class TextReaderTest {

  #[Test]
  public function can_create_with_string() {
    new TextReader('');
  }

  #[Test]
  public function can_create_with_stream() {
    new TextReader(new MemoryInputStream(''));
  }

  #[Test]
  public function can_create_with_channel() {
    new TextReader(new class() implements Channel {
      public function in() { return new MemoryInputStream(''); }
      public function out() { return new MemoryOutputStream(); }
    });
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function raises_exception_for_incorrect_constructor_argument() {
    new TextReader(null);
  }

  /**
   * Returns a text reader for a given input string.
   *
   * @param   string $str
   * @param   string $charset
   * @return  io.streams.TextReader
   */
  private function newReader($str, $charset= \xp::ENCODING) {
    return new TextReader(new MemoryInputStream($str), $charset);
  }

  /**
   * Returns a stream that does not support seeking
   *
   * @return  io.streams.InputStream
   */
  private function unseekableStream() {
    return new class() implements InputStream {
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
    };
  }

  #[Test]
  public function readOne() {
    Assert::equals('H', $this->newReader('Hello', 'iso-8859-1')->read(1));
  }

  #[Test]
  public function readOneUtf8() {
    Assert::equals('Ü', $this->newReader('Übercoder', 'utf-8')->read(1));
  }

  #[Test]
  public function readLength() {
    Assert::equals('Hello', $this->newReader('Hello')->read(5));
  }

  #[Test]
  public function readLengthUtf8() {
    Assert::equals('Übercoder', $this->newReader('Übercoder', 'utf-8')->read(9));
  }

  #[Test, Expect(FormatException::class)]
  public function readBrokenUtf8() {
    $this->newReader("Hello \334|", 'utf-8')->read(0x1000);
  }

  #[Test, Expect(FormatException::class)]
  public function readMalformedUtf8() {
    $this->newReader("Hello \334bercoder", 'utf-8')->read(0x1000);
  }

  #[Test]
  public function readingDoesNotContinueAfterBrokenCharacters() {
    $r= $this->newReader("Hello \334bercoder\n".str_repeat('*', 512), 'utf-8');
    try {
      $r->read(10);
      $this->fail('No exception caught', null, 'lang.FormatException');
    } catch (FormatException $expected) {
      // OK
    }
    Assert::null($r->read(512));
  }

  #[Test, Values(['ˈçiːna', 'ˈkiːna', '中國 / 中国', 'Zhōngguó'])]
  public function readPreviouslyUnconvertible($value) {
    Assert::equals($value, $this->newReader($value, 'utf-8')->read());
  }

  #[Test]
  public function read() {
    Assert::equals('Hello', $this->newReader('Hello')->read());
  }

  #[Test]
  public function encodedBytesOnly() {
    Assert::equals(
      str_repeat('Ü', 1024), 
      $this->newReader(str_repeat("\303\234", 1024), 'utf-8')->read(1024)
    );
  }

  #[Test]
  public function readAfterEnd() {
    $r= $this->newReader('Hello');
    Assert::equals('Hello', $r->read(5));
    Assert::null($r->read());
  }

  #[Test]
  public function readMultipleAfterEnd() {
    $r= $this->newReader('Hello');
    Assert::equals('Hello', $r->read(5));
    Assert::null($r->read());
    Assert::null($r->read());
  }

  #[Test]
  public function readLineAfterEnd() {
    $r= $this->newReader('Hello');
    Assert::equals('Hello', $r->read(5));
    Assert::null($r->readLine());
  }

  #[Test]
  public function readLineMultipleAfterEnd() {
    $r= $this->newReader('Hello');
    Assert::equals('Hello', $r->read(5));
    Assert::null($r->readLine());
    Assert::null($r->readLine());
  }

  #[Test]
  public function readZero() {
    Assert::equals('', $this->newReader('Hello')->read(0));
  }

  #[Test]
  public function readLineEmptyInput() {
    Assert::null($this->newReader('')->readLine());
  }

  #[Test, Values(["Hello\nWorld\n", "Hello\rWorld\r", "Hello\r\nWorld\r\n", "Hello\nWorld", "Hello\rWorld", "Hello\r\nWorld"])]
  public function readLines($value) {
    $r= $this->newReader($value);
    Assert::equals('Hello', $r->readLine());
    Assert::equals('World', $r->readLine());
    Assert::null($r->readLine());
  }

  #[Test, Values(["1\n2\n", "1\r2\r", "1\r\n2\r\n", "1\n2", "1\r2", "1\r\n2\r\n"])]
  public function readLinesWithSingleCharacter($value) {
    $r= $this->newReader($value);
    Assert::equals('1', $r->readLine());
    Assert::equals('2', $r->readLine());
    Assert::null($r->readLine());
  }

  #[Test]
  public function readEmptyLine() {
    $r= $this->newReader("Hello\n\nWorld");
    Assert::equals('Hello', $r->readLine());
    Assert::equals('', $r->readLine());
    Assert::equals('World', $r->readLine());
    Assert::null($r->readLine());
  }

  #[Test]
  public function readLinesUtf8() {
    $r= $this->newReader("\303\234ber\nCoder", 'utf-8');
    Assert::equals('Über', $r->readLine());
    Assert::equals('Coder', $r->readLine());
    Assert::null($r->readLine());
  }
  
  #[Test]
  public function readLinesAutodetectIso88591() {
    $r= $this->newReader("\334bercoder", null);
    Assert::equals('Übercoder', $r->readLine());
  }
  
  #[Test]
  public function readShortLinesAutodetectIso88591() {
    $r= $this->newReader("\334", null);
    Assert::equals('Ü', $r->readLine());
  }

  #[Test]
  public function readLinesAutodetectUtf8() {
    $r= $this->newReader("\357\273\277\303\234bercoder", null);
    Assert::equals('Übercoder', $r->readLine());
  }

  #[Test]
  public function autodetectUtf8() {
    $r= $this->newReader("\357\273\277\303\234bercoder", null);
    Assert::equals('utf-8', $r->charset());
  }

  #[Test, Values(["\376\377\000\334\000b\000e\000r\000c\000o\000d\000e\000r", "\376\377\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\015", "\376\377\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\015\000\012", "\376\377\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\012"])]
  public function readLinesAutodetectUtf16BE($value) {
    $r= $this->newReader($value, null);
    Assert::equals('Übercoder', $r->readLine());
  }

  #[Test]
  public function autodetectUtf16Be() {
    $r= $this->newReader("\376\377\000\334\000b\000e\000r\000c\000o\000d\000e\000r", null);
    Assert::equals('utf-16be', $r->charset());
  }
  
  #[Test, Values(["\377\376\334\000b\000e\000r\000c\000o\000d\000e\000r\000", "\377\376\334\000b\000e\000r\000c\000o\000d\000e\000r\000\015\000", "\377\376\334\000b\000e\000r\000c\000o\000d\000e\000r\000\012\000", "\377\376\334\000b\000e\000r\000c\000o\000d\000e\000r\000\015\000\012\000"])]
  public function readLinesAutodetectUtf16Le($value) {
    $r= $this->newReader($value, null);
    Assert::equals('Übercoder', $r->readLine());
  }

  #[Test]
  public function autodetectUtf16Le() {
    $r= $this->newReader("\377\376\334\000b\000e\000r\000c\000o\000d\000e\000r\000", null);
    Assert::equals('utf-16le', $r->charset());
  }

  #[Test, Values(["L\000\000\0001\000\000\000\n\000\000\000L\000\000\0002\000\000\000", "L\000\000\0001\000\000\000\r\000\000\000L\000\000\0002\000\000\000", "L\000\000\0001\000\000\000\r\000\000\000\n\000\000\000L\000\000\0002\000\000\000"])]
  public function readLinesUtf32Le($value) {
    $r= $this->newReader($value, 'utf-32le');
    Assert::equals(['L1', 'L2'], [$r->readLine(), $r->readLine()]);
  }

  #[Test, Values(["\000\000\000L\000\000\0001\000\000\000\n\000\000\000L\000\000\0002", "\000\000\000L\000\000\0001\000\000\000\r\000\000\000L\000\000\0002", "\000\000\000L\000\000\0001\000\000\000\r\000\000\000\n\000\000\000L\000\000\0002"])]
  public function readLinesUtf32Be($value) {
    $r= $this->newReader($value, 'utf-32be');
    Assert::equals(['L1', 'L2'], [$r->readLine(), $r->readLine()]);
  }

  #[Test]
  public function defaultCharsetIsIso88591() {
    $r= $this->newReader('Übercoder', null);
    Assert::equals('iso-8859-1', $r->charset());
  }

  #[Test]
  public function bufferProblem() {
    $r= $this->newReader("Hello\rX");
    Assert::equals('Hello', $r->readLine());
    Assert::equals('X', $r->readLine());
    Assert::null($r->readLine());
  }

  #[Test]
  public function closingTwice() {
    $r= $this->newReader('');
    $r->close();
    $r->close();
  }

  #[Test]
  public function reset() {
    $r= $this->newReader('ABC');
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));

  }
  #[Test]
  public function resetWithBuffer() {
    $r= $this->newReader("Line 1\rLine 2");
    Assert::equals('Line 1', $r->readLine());    // We have "\n" in the buffer
    $r->reset();
    Assert::equals('Line 1', $r->readLine());
    Assert::equals('Line 2', $r->readLine());
  }

  #[Test]
  public function resetUtf8() {
    $r= $this->newReader("\357\273\277ABC", null);
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));
  }

  #[Test]
  public function resetUtf8WithoutBOM() {
    $r= $this->newReader('ABC', 'utf-8');
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));
  }

  #[Test]
  public function resetUtf16Le() {
    $r= $this->newReader("\377\376A\000B\000C\000", null);
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));
  }

  #[Test]
  public function resetUtf16LeWithoutBOM() {
    $r= $this->newReader("A\000B\000C\000", 'utf-16le');
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));
  }

  #[Test]
  public function resetUtf16Be() {
    $r= $this->newReader("\376\377\000A\000B\000C", null);
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));
  }

  #[Test]
  public function resetUtf16BeWithoutBOM() {
    $r= $this->newReader("\000A\000B\000C", 'utf-16be');
    Assert::equals('ABC', $r->read(3));
    $r->reset();
    Assert::equals('ABC', $r->read(3));
  }

  #[Test, Expect(['class' => IOException::class, 'withMessage' => 'Underlying stream does not support seeking'])]
  public function resetUnseekable() {
    $r= new TextReader($this->unseekableStream());
    $r->reset();
  }

  #[Test]
  public function readOneWithAutoDetectedIso88591Charset() {
    Assert::equals('H', $this->newReader('Hello', null)->read(1));
  }

  #[Test]
  public function readOneWithAutoDetectedUtf16BECharset() {
    Assert::equals('H', $this->newReader("\376\377\0H\0e\0l\0l\0o", null)->read(1));
  }

  #[Test]
  public function readOneWithAutoDetectedUtf16LECharset() {
    Assert::equals('H', $this->newReader("\377\376H\0e\0l\0l\0o\0", null)->read(1));
  }

  #[Test]
  public function readOneWithAutoDetectedUtf8Charset() {
    Assert::equals('H', $this->newReader("\357\273\277Hello", null)->read(1));
  }

  #[Test]
  public function readLineWithAutoDetectedIso88591Charset() {
    Assert::equals('H', $this->newReader("H\r\n", null)->readLine());
  }

  #[Test]
  public function readLineWithAutoDetectedUtf16BECharset() {
    Assert::equals('H', $this->newReader("\376\377\0H\0\r\0\n", null)->readLine());
  }

  #[Test]
  public function readLineWithAutoDetectedUtf16LECharset() {
    Assert::equals('H', $this->newReader("\377\376H\0\r\0\n\0", null)->readLine());
  }

  #[Test]
  public function readLineWithAutoDetectedUtf8Charset() {
    Assert::equals('H', $this->newReader("\357\273\277H\r\n", null)->readLine());
  }

  #[Test]
  public function readLineEmptyInputWithAutoDetectedIso88591Charset() {
    Assert::null($this->newReader('', null)->readLine());
  }

  #[Test, Values([["\377", 'ÿ'], ["\377\377", 'ÿÿ'], ["\377\377\377", 'ÿÿÿ']])]
  public function readNonBOMInputWithAutoDetectedIso88591Charset($bytes, $characters) {
    Assert::equals($characters, $this->newReader($bytes, null)->read(0xFF));
  }

  #[Test, Values([["\377", 'ÿ'], ["\377\377", 'ÿÿ'], ["\377\377\377", 'ÿÿÿ']])]
  public function readLineNonBOMInputWithAutoDetectedIso88591Charset($bytes, $characters) {
    Assert::equals($characters, $this->newReader($bytes, null)->readLine(0xFF));
  }

  #[Test]
  public function lines() {
    Assert::instance(LinesIn::class, $this->newReader('')->lines());
  }

  #[Test]
  public function can_iterate_twice_on_seekable() {
    $reader= $this->newReader("A\nB");
    Assert::equals(
      [[1 => 'A', 2 => 'B'], [1 => 'A', 2 => 'B']],
      [iterator_to_array($reader->lines()), iterator_to_array($reader->lines())]
    );
  }

  #[Test]
  public function iteration_after_reading() {
    $reader= $this->newReader("A\nB");
    $reader->read();
    Assert::equals([1 => 'A', 2 => 'B'], iterator_to_array($reader->lines()));
  }

  #[Test]
  public function iteration_after_reading_a_line() {
    $reader= $this->newReader("A\nB");
    $reader->readLine();
    Assert::equals([1 => 'A', 2 => 'B'], iterator_to_array($reader->lines()));
  }

  #[Test]
  public function can_only_iterate_unseekable_once() {
    $reader= new TextReader($this->unseekableStream());
    Assert::equals([1 => 'A', 2 => 'B'], iterator_to_array($reader->lines()));
    try {
      iterator_to_array($reader->lines());
      $this->fail('No exception raised', null, 'io.IOException');
    } catch (\io\IOException $expected) {
      // OK
    }
  }

  #[Test]
  public function read_and_readLine() {
    $reader= $this->newReader("AL1\nBBL2");
    Assert::equals(['A', 'L1', 'BB', 'L2'], [
      $reader->read(1),
      $reader->readLine(),
      $reader->read(2),
      $reader->readLine()
    ]);
  }

  #[Test]
  public function readLine_and_read() {
    $reader= $this->newReader("L1\nABL2");
    Assert::equals(['L1', 'A', 'B', 'L2'], [
      $reader->readLine(),
      $reader->read(1),
      $reader->read(1),
      $reader->readLine()
    ]);
  }
}