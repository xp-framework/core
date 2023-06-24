<?php namespace net\xp_framework\unittest\io;

use io\streams\{MemoryInputStream, Streams};
use io\{EncapsedStream, File};
use lang\{IllegalAccessException, IllegalStateException};
use unittest\Assert;
use unittest\{Expect, Test};

class EncapsedStreamTest {
    
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
  
  #[Test, Expect(IllegalStateException::class)]
  public function file_given_must_be_open() {
    new EncapsedStream(new File('irrelevant.txt'), 0, 0);
  }
  
  #[Test]
  public function open_for_reading() {
    $this->newStream()->open(File::READ);
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function cannot_open_for_writing() {
    $this->newStream()->open(File::WRITE);
  }
  
  #[Test]
  public function read() {
    Assert::equals('Test', $this->newStream('"Test"', 1, 4)->read());
  }

  #[Test]
  public function readChar() {
    Assert::equals('T', $this->newStream('"Test"', 1, 4)->readChar());
  }

  #[Test]
  public function readLine_with_line_ending() {
    Assert::equals('Test', $this->newStream("@Test\n", 1, 4)->readLine());
  }

  #[Test]
  public function readLine_with_no_line_ending() {
    Assert::equals('Test', $this->newStream('@Test!', 1, 4)->readLine());
  }

  #[Test]
  public function gets_with_line_ending() {
    Assert::equals('Test', $this->newStream("@Test\n", 1, 4)->gets());
  }

  #[Test]
  public function gets_with_no_line_ending() {
    Assert::equals('Test', $this->newStream('@Test!', 1, 4)->gets());
  }
  
  #[Test]
  public function seek() {
    $fixture= $this->newStream('1234567890', 1, 8);
    $fixture->seek(6);
    Assert::equals('89', $fixture->read());
  }
  
  #[Test]
  public function eof_after_seeking() {
    $fixture= $this->newStream('1234567890', 1, 8);
    $fixture->seek(6);
    Assert::false($fixture->eof());
  }

  #[Test]
  public function eof_after_seeking_until_end() {
    $fixture= $this->newStream('1234567890', 1, 8);
    $fixture->seek(8);
    Assert::true($fixture->eof());
  }
  
  #[Test]
  public function reading_lines_using_readLine() {
    $lines= "Line 1\nLine 2\nLine 3\nLine 4\n";
    $oneLine= strlen("Line *\n");

    $fixture= $this->newStream($lines, $oneLine, $oneLine * 2);
    Assert::equals('Line 2', $fixture->readLine());
    Assert::equals('Line 3', $fixture->readLine());
    Assert::equals(false, $fixture->readLine());
  }  

  #[Test]
  public function reading_lines_using_gets() {
    $lines= "Line 1\nLine 2\nLine 3\nLine 4\n";
    $oneLine= strlen("Line *\n");

    $fixture= $this->newStream($lines, $oneLine, $oneLine * 2);
    Assert::equals("Line 2\n", $fixture->gets());
    Assert::equals("Line 3\n", $fixture->gets());
    Assert::equals(false, $fixture->gets());
  }
}