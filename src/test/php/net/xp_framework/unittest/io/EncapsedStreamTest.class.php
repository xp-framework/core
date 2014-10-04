<?php namespace net\xp_framework\unittest\io;

use unittest\TestCase;
use io\EncapsedStream;

/**
 * TestCase
 *
 * @see   xp://io.EncapsedStream
 */
class EncapsedStreamTest extends TestCase {
    
  /**
   * Returns a new buffer
   */
  public function newBuffer($contents= '', $start= 0, $length= 0) {
    $buffer= new Buffer($contents);
    $buffer->open(FILE_MODE_READ);
    return new EncapsedStream($buffer, $start, $length);
  }
  
  #[@test, @expect('lang.IllegalStateException')]
  public function cannot_create_with_non_open_buffer() {
    new EncapsedStream(new Buffer(), 0, 0);
  }
  
  #[@test]
  public function open_for_reading() {
    $this->newBuffer()->open(FILE_MODE_READ);
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function cannot_open_for_writing() {
    $this->newBuffer()->open(FILE_MODE_WRITE);
  }
  
  #[@test]
  public function read() {
    $this->assertEquals('Test', $this->newBuffer('"Test"', 1, 4)->readLine());
  }
  
  #[@test]
  public function gets() {
    $this->assertEquals('Test', $this->newBuffer('"Test"', 1, 4)->gets());
  }
  
  #[@test]
  public function seek() {
    $fixture= $this->newBuffer('1234567890', 1, 8);
    $fixture->seek(6);
    $this->assertEquals('89', $fixture->read());
  }
  
  #[@test]
  public function eof_after_seeking() {
    $fixture= $this->newBuffer('1234567890', 1, 8);
    $fixture->seek(6);
    $this->assertFalse($fixture->eof());
  }

  #[@test]
  public function eof_after_seeking_until_end() {
    $fixture= $this->newBuffer('1234567890', 1, 8);
    $fixture->seek(8);
    $this->assertTrue($fixture->eof());
  }
  
  #[@test]
  public function reading_lines() {
    $stream= new Buffer(
      "This is the first line.\n".
      "This is the second line.\n".
      "And there is a third one.\n"
    );
    $stream->open(FILE_MODE_READ);
    
    $fixture= new EncapsedStream($stream, 5, $stream->size()- 35);
    $this->assertEquals('is the first line.', $fixture->readLine());
    $this->assertEquals('This is the second li', $fixture->readLine());
    $this->assertEquals('', $fixture->readLine());
  }  
}
