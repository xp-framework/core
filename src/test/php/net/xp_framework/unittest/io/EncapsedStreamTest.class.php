<?php namespace net\xp_framework\unittest\io;

use io\{EncapsedStream, File, Streams};
use io\streams\MemoryInputStream;
use lang\{IllegalAccessException, IllegalStateException};

class EncapsedStreamTest extends \unittest\TestCase {
    
  /**
   * Returns a new EncapsedStream instance
   *
   * @return  io.EncapsedStream
   */
  public function newStream($contents= '', $start= 0, $length= 0) {
    return new EncapsedStream(
      new File(Streams::readableFd(new MemoryInputStream($contents))),
      $start,
      $length
    );
  }
  
  #[@test, @expect(IllegalStateException::class)]
  public function file_given_must_be_open() {
    new EncapsedStream(new File('irrelevant.txt'), 0, 0);
  }
  
  #[@test]
  public function open_for_reading() {
    $this->newStream()->open(File::READ);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function cannot_open_for_writing() {
    $this->newStream()->open(File::WRITE);
  }
  
  #[@test]
  public function read() {
    $this->assertEquals('Test', $this->newStream('"Test"', 1, 4)->read());
  }

  #[@test]
  public function readChar() {
    $this->assertEquals('T', $this->newStream('"Test"', 1, 4)->readChar());
  }

  #[@test]
  public function readLine_with_line_ending() {
    $this->assertEquals('Test', $this->newStream("@Test\n", 1, 4)->readLine());
  }

  #[@test]
  public function readLine_with_no_line_ending() {
    $this->assertEquals('Test', $this->newStream('@Test!', 1, 4)->readLine());
  }

  #[@test]
  public function gets_with_line_ending() {
    $this->assertEquals('Test', $this->newStream("@Test\n", 1, 4)->gets());
  }

  #[@test]
  public function gets_with_no_line_ending() {
    $this->assertEquals('Test', $this->newStream('@Test!', 1, 4)->gets());
  }
  
  #[@test]
  public function seek() {
    $fixture= $this->newStream('1234567890', 1, 8);
    $fixture->seek(6);
    $this->assertEquals('89', $fixture->read());
  }
  
  #[@test]
  public function eof_after_seeking() {
    $fixture= $this->newStream('1234567890', 1, 8);
    $fixture->seek(6);
    $this->assertFalse($fixture->eof());
  }

  #[@test]
  public function eof_after_seeking_until_end() {
    $fixture= $this->newStream('1234567890', 1, 8);
    $fixture->seek(8);
    $this->assertTrue($fixture->eof());
  }
  
  #[@test]
  public function reading_lines_using_readLine() {
    $lines= "Line 1\nLine 2\nLine 3\nLine 4\n";
    $oneLine= strlen("Line *\n");

    $fixture= $this->newStream($lines, $oneLine, $oneLine * 2);
    $this->assertEquals('Line 2', $fixture->readLine());
    $this->assertEquals('Line 3', $fixture->readLine());
    $this->assertEquals(false, $fixture->readLine());
  }  

  #[@test]
  public function reading_lines_using_gets() {
    $lines= "Line 1\nLine 2\nLine 3\nLine 4\n";
    $oneLine= strlen("Line *\n");

    $fixture= $this->newStream($lines, $oneLine, $oneLine * 2);
    $this->assertEquals("Line 2\n", $fixture->gets());
    $this->assertEquals("Line 3\n", $fixture->gets());
    $this->assertEquals(false, $fixture->gets());
  }
}
