<?php namespace net\xp_framework\unittest\io\streams;

use unittest\TestCase;
use io\streams\StringReader;
use io\streams\MemoryInputStream;

class StringReaderTest extends TestCase {

  #[@test]
  public function readLine() {
    $line1= 'This is a test';
    $line2= 'Onother line!';
    
    $stream= new StringReader(new MemoryInputStream($line1."\n".$line2));

    $this->assertEquals($line1, $stream->readLine());
    $this->assertEquals($line2, $stream->readLine());
  }
  
  #[@test]
  public function readLineWithEmptyLine() {
    $stream= new StringReader(new MemoryInputStream("\n"));

    $this->assertEquals('', $stream->readLine());
  }

  #[@test]
  public function readLineWithEmptyLines() {
    $stream= new StringReader(new MemoryInputStream("\n\n\nHello\n\n"));

    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('', $stream->readLine());
    $this->assertEquals('Hello', $stream->readLine());
    $this->assertEquals('', $stream->readLine());
  }
  
  #[@test]
  public function readLineWithSingleLine() {
    $stream= new StringReader(new MemoryInputStream($line= 'This is a test'));

    $this->assertEquals($line, $stream->readLine());
  }
  
  #[@test]
  public function readLineWithZeros() {
    $stream= new StringReader(new MemoryInputStream($line= 'Line containing 0 characters'));
    
    $this->assertEquals($line, $stream->readLine());
  }

  #[@test]
  public function read() {
    $stream= new StringReader(new MemoryInputStream($line= 'Hello World'));
    
    $this->assertEquals('Hello', $stream->read(5));
    $this->assertEquals(' ', $stream->read(1));
    $this->assertEquals('World', $stream->read(5));
  }

  #[@test]
  public function readAll() {
    $stream= new StringReader(new MemoryInputStream($line= 'Hello World'));
    
    $this->assertEquals('Hello World', $stream->read());
  }

  #[@test]
  public function readAfterReadingAll() {
    $stream= new StringReader(new MemoryInputStream($line= 'Hello World'));
    
    $this->assertEquals('Hello World', $stream->read());
    $this->assertEquals(null, $stream->read());
  }

  #[@test]
  public function readLineAfterReadingAllLines() {
    $stream= new StringReader(new MemoryInputStream($line= 'Hello World'."\n"));
    
    $this->assertEquals('Hello World', $stream->readLine());
    $this->assertEquals(null, $stream->readLine());
  }

  #[@test]
  public function readAfterReadingAllLines() {
    $stream= new StringReader(new MemoryInputStream($line= 'Hello World'."\n"));
    
    $this->assertEquals('Hello World', $stream->readLine());
    $this->assertEquals(null, $stream->read());
  }
}
