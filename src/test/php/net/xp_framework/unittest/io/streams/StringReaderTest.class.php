<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\{InputStream, MemoryInputStream, StringReader};
use lang\IllegalStateException;
use unittest\{Test, TestCase, Values};

class StringReaderTest extends TestCase {

  #[Test, Values(["\n", "\r", "\r\n"])]
  public function read_empty_line($newLine) {
    $stream= new StringReader(new MemoryInputStream($newLine));
    $this->assertEquals('', $stream->readLine());
  }

  #[Test]
  public function read_single_line() {
    $stream= new StringReader(new MemoryInputStream($line= 'This is a test'));
    $this->assertEquals($line, $stream->readLine());
  }

  #[Test, Values(["\n", "\r", "\r\n"])]
  public function read_lines($newLine) {
    $line1= 'This is a test';
    $line2= 'Another line!';
    $stream= new StringReader(new MemoryInputStream($line1.$newLine.$line2));

    $this->assertEquals($line1, $stream->readLine());
    $this->assertEquals($line2, $stream->readLine());
  }

  #[Test, Values(["\n\n\nHello\n\n", "\r\r\rHello\r\r", "\r\n\r\n\r\nHello\r\n\r\n",])]
  public function read_lines_with_empty_lines_inbetween($input) {
    $stream= new StringReader(new MemoryInputStream($input));
    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('Hello', $stream->readLine());
    $this->assertEquals('', $stream->readLine());
  }
  
  #[Test]
  public function read_line_with_zero() {
    $stream= new StringReader(new MemoryInputStream($line= 'Line containing 0 characters'));
    $this->assertEquals($line, $stream->readLine());
  }

  #[Test]
  public function read() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    $this->assertEquals('Hello', $stream->read(5));
    $this->assertEquals(' ', $stream->read(1));
    $this->assertEquals('World', $stream->read(5));
  }

  #[Test]
  public function readLine_after_reading() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    $this->assertEquals('Hello', $stream->read(5));
    $this->assertEquals(' ', $stream->read(1));
    $this->assertEquals('World', $stream->readLine());
  }

  #[Test]
  public function read_after_readLine() {
    $stream= new StringReader(new MemoryInputStream("Hello\n\0\nWorld\n"));
    $this->assertEquals('Hello', $stream->readLine());
    $this->assertEquals("\0", $stream->read(1));
    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('World', $stream->readLine());
  }

  #[Test]
  public function read_all() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    $this->assertEquals('Hello World', $stream->read());
  }

  #[Test]
  public function read_after_reading_all() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    $this->assertEquals('Hello World', $stream->read());
    $this->assertNull($stream->read());
  }

  #[Test]
  public function readLine_after_reading_all() {
    $stream= new StringReader(new MemoryInputStream('Hello World'));
    $this->assertEquals('Hello World', $stream->read());
    $this->assertNull($stream->readLine());
  }

  #[Test, Values(["Hello World\n", "Hello World"])]
  public function readLine_after_reading_all_lines($input) {
    $stream= new StringReader(new MemoryInputStream($input));
    $this->assertEquals('Hello World', $stream->readLine());
    $this->assertNull($stream->readLine());
  }

  #[Test, Values(["Hello World\n", "Hello World"])]
  public function read_after_reading_all_lines($input) {
    $stream= new StringReader(new MemoryInputStream($input));
    $this->assertEquals('Hello World', $stream->readLine());
    $this->assertNull($stream->read());
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

    $this->assertEquals('Test', $stream->readLine());
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
    $this->assertEquals($expected, [$reader->readLine(), $input->chunks]);
  }
}