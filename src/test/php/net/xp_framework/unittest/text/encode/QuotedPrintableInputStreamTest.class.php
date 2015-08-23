<?php namespace net\xp_framework\unittest\text\encode;

use unittest\TestCase;
use io\streams\InputStream;
use io\streams\MemoryInputStream;
use text\encode\QuotedPrintableInputStream;
use io\IOException;

/**
 * Test QuotedPrintable decoder
 *
 * @see   xp://text.encode.QuotedPrintableInputStream
 */
class QuotedPrintableInputStreamTest extends TestCase {

  #[@test]
  public function singleRead() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('Hello'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('Hello', $chunk);
  }

  #[@test]
  public function multipleReads() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('Hello World'));
    $chunk1= $stream->read(5);
    $chunk2= $stream->read(1);
    $chunk3= $stream->read(5);
    $stream->close();
    $this->assertEquals('Hello', $chunk1);
    $this->assertEquals(' ', $chunk2);
    $this->assertEquals('World', $chunk3);
  }

  #[@test]
  public function umlaut() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('=DCbercoder'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('Übercoder', $chunk);
  }

  #[@test]
  public function space() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('Space between'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('Space between', $chunk);
  }

  #[@test]
  public function encodedSpace() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('Space=20between'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('Space between', $chunk);
  }

  #[@test]
  public function tab() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream("Tab\tbetween"));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals("Tab\tbetween", $chunk);
  }

  #[@test]
  public function encodedTab() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('Tab=09between'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals("Tab\tbetween", $chunk);
  }

  #[@test]
  public function softLineBreak() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream(str_repeat('1', 75)."=\n".str_repeat('2', 75)));
    $chunk= $stream->read(150);
    $stream->close();
    $this->assertEquals(str_repeat('1', 75).str_repeat('2', 75), $chunk);
  }

  #[@test]
  public function spaceAtEnd() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('Hello '));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('Hello ', $chunk);
  }

  #[@test]
  public function chunkedRead() {
    $expected= 'Hello Übercoder & World';
    $stream= new QuotedPrintableInputStream(newinstance(InputStream::class, [['Hello =', 'DCbercoder=', "\n", ' & World']], '{
      protected $chunks;
      
      public function __construct(array $chunks) {
        $this->chunks= $chunks;
      }
      
      public function read($limit= 8192) {
        return array_shift($this->chunks);
      }
      
      public function available() {
        return sizeof($this->chunks) > 0 ? 1 : 0;
      }
      
      public function close() {
        $this->chunks= [];
      }
    }'));
    $chunk= $stream->read(strlen($expected));
    $stream->close();
    $this->assertEquals($expected, $chunk);
  }

  #[@test]
  public function equalsSign() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('A=3D1'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('A=1', $chunk);
  }

  #[@test]
  public function lowerCaseEscapeSequence() {
    $stream= new QuotedPrintableInputStream(new MemoryInputStream('=3d'));
    $chunk= $stream->read();
    $stream->close();
    $this->assertEquals('=', $chunk);
  }
  
  #[@test, @expect(IOException::class)]
  public function invalidByteSequence() {
    (new QuotedPrintableInputStream(new MemoryInputStream('Hell=XX')))->read();
  }
}
