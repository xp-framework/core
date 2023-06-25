<?php namespace io\unittest;

use io\streams\{InputStream, MemoryInputStream, StringReader};
use lang\IllegalStateException;
use test\{Assert, Test, Values};

class StringReaderTest {

  #[Test, Values(["\n", "\r", "\r\n"])]
  public function read_empty_line($newLine) {
    $stream= new StringReader(new MemoryInputStream($newLine));
    Assert::equals('', $stream->readLine());
  }

  #[Test]
  public function read_single_line() {
    $stream= new StringReader(new MemoryInputStream($line= 'This is a test'));
    Assert::equals($line, $stream->readLine());
  }

  #[Test, Values(["\n", "\r", "\r\n"])]
  public function read_lines($newLine) {
    $line1= 'This is a test';
    $line2= 'Another line!';
    $stream= new StringReader(new MemoryInputStream($line1.$newLine.$line2));

    Assert::equals($line1, $stream->readLine());
    Assert::equals($line2, $stream->readLine());
  }

  #[Test, Values(["\n\n\nHello\n\n", "\r\r\rHello\r\r", "\r\n\r\n\r\nHello\r\n\r\n",])]
  public function read_lines_with_empty_lines_inbetween($input) {
    $stream= new StringReader(new MemoryInputStream($input));
    Assert::equals('', $stream->readLine());
    Assert::equals('', $stream->readLine());
    Assert::equals('', $stream->readLine());
    Assert::equals('Hello', $stream->readLine());
    Assert::equals('', $stream->readLine());
  }
  
  #[Test]
  public function read_line_with_zero() {
    $stream= new StringReader(new MemoryInputStream($line= 'Line containing 0 characters'));
    Assert::equals($line, $stream->readLine());
  }

  #[Test]
  public function read() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    Assert::equals('Hello', $stream->read(5));
    Assert::equals(' ', $stream->read(1));
    Assert::equals('World', $stream->read(5));
  }

  #[Test]
  public function readLine_after_reading() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    Assert::equals('Hello', $stream->read(5));
    Assert::equals(' ', $stream->read(1));
    Assert::equals('World', $stream->readLine());
  }

  #[Test]
  public function read_after_readLine() {
    $stream= new StringReader(new MemoryInputStream("Hello\n\0\nWorld\n"));
    Assert::equals('Hello', $stream->readLine());
    Assert::equals("\0", $stream->read(1));
    Assert::equals('', $stream->readLine());
    Assert::equals('World', $stream->readLine());
  }

  #[Test]
  public function read_all() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    Assert::equals('Hello World', $stream->read());
  }

  #[Test]
  public function read_after_reading_all() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    Assert::equals('Hello World', $stream->read());
    Assert::null($stream->read());
  }

  #[Test]
  public function readLine_after_reading_all() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    Assert::equals('Hello World', $stream->read());
    Assert::null($stream->readLine());
  }

  #[Test, Values(["Hello World\n", "Hello World"])]
  public function readLine_after_reading_all_lines($input) {
    $stream= new StringReader(new MemoryInputStream($input));
    Assert::equals('Hello World', $stream->readLine());
    Assert::null($stream->readLine());
  }

  #[Test, Values(["Hello World\n", "Hello World"])]
  public function read_after_reading_all_lines($input) {
    $stream= new StringReader(new MemoryInputStream($input));
    Assert::equals('Hello World', $stream->readLine());
    Assert::null($stream->read());
  }

  #[Test]
  public function readLine_calls_read_once_when_read_returns_line() {
    $stream= new StringReader(newinstance(InputStream::class, [], [
      'called'    => 0,
      'available' => function() { return 1; },
      'close'     => function() { return true; },
      'read'      => function($limit= 8192) {
        if ($this->called > 0) {
          throw new IllegalStateException('Should only call read() once');
        }
        $this->called++;
        return "Test\n";
      }
    ]));

    Assert::equals('Test', $stream->readLine());
  }

  #[Test, Values([[['Test', "\n"], ['Test', []]], [['Test', "\r"], ['Test', []]], [['Test', "\r\n"], ['Test', []]], [['Test', "\n", 'Rest'], ['Test', ['Rest']]], [['Test', '1', '2', '3', "\n", 'Rest'], ['Test123', ['Rest']]]])]
  public function readLine_continues_reading_until_newline($chunks, $expected) {
    $input= newinstance(InputStream::class, [$chunks], [
      'chunks'      => [],
      '__construct' => function($chunks) { $this->chunks= $chunks; },
      'available'   => function() { return sizeof($this->chunks); },
      'close'       => function() { $this->chunks= []; },
      'read'        => function($limit= 8192) { return array_shift($this->chunks); }
    ]);

    $reader= new StringReader($input);
    Assert::equals($expected, [$reader->readLine(), $input->chunks]);
  }
}