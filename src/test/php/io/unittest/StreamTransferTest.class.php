<?php namespace io\unittest;

use io\IOException;
use io\streams\{InputStream, MemoryInputStream, MemoryOutputStream, OutputStream, StreamTransfer};
use test\{Assert, Test, Values};

class StreamTransferTest {

  /** Returns an uncloseable input stream */
  protected function uncloseableInputStream() {
    return new class() implements InputStream {
      public function read($length= 8192) { }
      public function available() { }
      public function close() { throw new \io\IOException("Close error"); }
    };
  }

  /** Returns a closeable input stream */
  protected function closeableInputStream() {
    return new class() implements InputStream {
      public $closed= false;
      public function read($length= 8192) { }
      public function available() { }
      public function close() { $this->closed= true; }
    };
  }
  
  /** Returns an uncloseable output stream */
  protected function uncloseableOutputStream() {
    return new class() implements OutputStream {
      public function write($data) { }
      public function flush() { }
      public function close() { throw new IOException('Close error'); }
    };
  }

  /** Returns a closeable output stream */
  protected function closeableOutputStream() {
    return new class() implements OutputStream {
      public $closed= false;
      public function write($data) { }
      public function flush() { }
      public function close() { $this->closed= true; }
    };
  }

  #[Test]
  public function transfer_all() {
    $out= new MemoryOutputStream();

    $s= new StreamTransfer(new MemoryInputStream('Hello'), $out);
    $size= $s->transferAll();

    Assert::equals('Hello', $out->bytes());
    Assert::equals(5, $size);
  }

  #[Test]
  public function transmit() {
    $out= new MemoryOutputStream();

    $s= new StreamTransfer(new MemoryInputStream('Hello'), $out);
    foreach ($s->transmit() as $yield) { }

    Assert::equals('Hello', $out->bytes());
  }

  #[Test, Values([[0, []], [1, [1]], [1024, [1024]], [1025, [1024, 1]], [2077, [1024, 1024, 29]]])]
  public function transmit_chunks($length, $chunks) {
    $out= new MemoryOutputStream();
    $data= str_repeat('*', $length);

    $s= new StreamTransfer(new MemoryInputStream($data), $out);
    $transmitted= [];
    foreach ($s->transmit(1024) as $yield) {
      $transmitted[]= $yield;
    }

    Assert::equals($data, $out->bytes());
    Assert::equals($chunks, $transmitted);
  }

  #[Test]
  public function nothing_available_after_transfer() {
    $in= new MemoryInputStream('Hello');

    $s= new StreamTransfer($in, new MemoryOutputStream());
    $s->transferAll();

    Assert::equals(0, $in->available());
  }

  #[Test]
  public function closing_twice() {
    $s= new StreamTransfer(new MemoryInputStream('Hello'), new MemoryOutputStream());
    $s->close();
    $s->close();
  }

  #[Test]
  public function close() {
    $in= $this->closeableInputStream();
    $out= $this->closeableOutputStream();

    (new StreamTransfer($in, $out))->close();

    Assert::true($in->closed, 'input closed');
    Assert::true($out->closed, 'output closed');
  }

  #[Test]
  public function closing_output_fails() {
    $in= $this->closeableInputStream();
    $out= $this->uncloseableOutputStream();

    try {
      (new StreamTransfer($in, $out))->close();
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (IOException $expected) {
      Assert::equals('Could not close output stream: Close error', $expected->getMessage());
    }
    
    Assert::true($in->closed, 'input closed');
  }

  #[Test]
  public function closing_input_fails() {
    $in= $this->uncloseableInputStream();
    $out= $this->closeableOutputStream();

    try {
      (new StreamTransfer($in, $out))->close();
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (IOException $expected) {
      Assert::equals('Could not close input stream: Close error', $expected->getMessage());
    }

    Assert::true($out->closed, 'output closed');
  }

  #[Test]
  public function closing_input_and_output_fails() {
    $in= $this->uncloseableInputStream();
    $out= $this->uncloseableOutputStream();

    try {
      (new StreamTransfer($in, $out))->close();
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (IOException $expected) {
      Assert::equals('Could not close input stream: Close error, Could not close output stream: Close error', $expected->getMessage());
    }
  }
}