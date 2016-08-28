<?php namespace net\xp_framework\unittest\io\streams;

use unittest\TestCase;
use io\streams\StreamTransfer;
use io\streams\InputStream;
use io\streams\OutputStream;
use io\streams\MemoryInputStream;
use io\streams\MemoryOutputStream;

/**
 * TestCase
 *
 * @see      xp://io.streams.StreamTransfer
 */
class StreamTransferTest extends TestCase {

  /**
   * Returns an uncloseable input stream
   *
   * @return  io.streams.InputStream
   */
  protected function uncloseableInputStream() {
    return new class() implements InputStream {
      public function read($length= 8192) { }
      public function available() { }
      public function close() { throw new \io\IOException("Close error"); }
    };
  }

  /**
   * Returns a closeable input stream
   *
   * @return  io.streams.InputStream
   */
  protected function closeableInputStream() {
    return new class() implements InputStream {
      public $closed= FALSE;
      public function read($length= 8192) { }
      public function available() { }
      public function close() { $this->closed= TRUE; }
    };
  }
  
  /**
   * Returns an uncloseable output stream
   *
   * @return  io.streams.OutputStream
   */
  protected function uncloseableOutputStream() {
    return new class() implements OutputStream {
      public function write($data) { }
      public function flush() { }
      public function close() { throw new \io\IOException("Close error"); }
    };
  }

  /**
   * Returns a closeable output stream
   *
   * @return  io.streams.OutputStream
   */
  protected function closeableOutputStream() {
    return new class() implements OutputStream {
      public $closed= FALSE;
      public function write($data) { }
      public function flush() { }
      public function close() { $this->closed= TRUE; }
    };
  }

  /**
   * Test
   *
   */
  #[@test]
  public function dataTransferred() {
    $out= new MemoryOutputStream();

    $s= new StreamTransfer(new MemoryInputStream('Hello'), $out);
    $s->transferAll();

    $this->assertEquals('Hello', $out->getBytes());
  }

  /**
   * Test
   *
   */
  #[@test]
  public function nothingAvailableAfterTransfer() {
    $in= new MemoryInputStream('Hello');

    $s= new StreamTransfer($in, new MemoryOutputStream());
    $s->transferAll();

    $this->assertEquals(0, $in->available());
  }

  /**
   * Test closing a stream twice has no effect.
   *
   * @see   xp://lang.Closeable#close
   */
  #[@test]
  public function closingTwice() {
    $s= new StreamTransfer(new MemoryInputStream('Hello'), new MemoryOutputStream());
    $s->close();
    $s->close();
  }

  /**
   * Test close() method
   *
   */
  #[@test]
  public function close() {
    $in= $this->closeableInputStream();
    $out= $this->closeableOutputStream();
    (new StreamTransfer($in, $out))->close();
    $this->assertTrue($in->closed, 'input closed');
    $this->assertTrue($out->closed, 'output closed');
  }

  /**
   * Test close() and exceptions
   *
   */
  #[@test]
  public function closingOutputFails() {
    $in= $this->closeableInputStream();
    $out= $this->uncloseableOutputStream();
    
    try {
      (new StreamTransfer($in, $out))->close();
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (\io\IOException $expected) {
      $this->assertEquals('Could not close output stream: Close error', $expected->getMessage());
    }
    
    $this->assertTrue($in->closed, 'input closed');
  }

  /**
   * Test close() and exceptions
   *
   */
  #[@test]
  public function closingInputFails() {
    $in= $this->uncloseableInputStream();
    $out= $this->closeableOutputStream();
    
    try {
      (new StreamTransfer($in, $out))->close();
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (\io\IOException $expected) {
      $this->assertEquals('Could not close input stream: Close error', $expected->getMessage());
    }

    $this->assertTrue($out->closed, 'output closed');
  }

  /**
   * Test close() and exceptions
   *
   */
  #[@test]
  public function closingInputAndOutputFails() {
    $in= $this->uncloseableInputStream();
    $out= $this->uncloseableOutputStream();
    
    try {
      (new StreamTransfer($in, $out))->close();
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (\io\IOException $expected) {
      $this->assertEquals('Could not close input stream: Close error, Could not close output stream: Close error', $expected->getMessage());
    }
  }
}
