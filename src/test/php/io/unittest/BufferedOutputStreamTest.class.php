<?php namespace io\unittest;

use io\streams\{BufferedOutputStream, MemoryOutputStream};
use test\{Assert, Test};

class BufferedOutputStreamTest {

  /**
   * Creates a new fixture
   *
   * @param  io.streams.MemoryOutputStream $mem
   * @return io.streams.BufferedOutputStream
   */
  private function newFixture($mem) { return new BufferedOutputStream($mem, 10); }

  #[Test]
  public function doNotFillBuffer() {
    $mem= new MemoryOutputStream();
    $out= $this->newFixture($mem);
    $out->write('Hello');
    Assert::equals('', $mem->bytes());
  }

  #[Test]
  public function fillBuffer() {
    $mem= new MemoryOutputStream();
    $out= $this->newFixture($mem);
    $out->write(str_repeat('*', 10));
    Assert::equals('', $mem->bytes());
  }

  #[Test]
  public function overFlowBuffer() {
    $mem= new MemoryOutputStream();
    $out= $this->newFixture($mem);
    $out->write('A long string that will fill the buffer');
    Assert::equals('A long string that will fill the buffer', $mem->bytes());
  }

  #[Test]
  public function flushed() {
    $mem= new MemoryOutputStream();
    $out= $this->newFixture($mem);
    $out->write('Hello');
    $out->flush();
    Assert::equals('Hello', $mem->bytes());
  }

  #[Test]
  public function flushedOnClose() {
    $mem= new MemoryOutputStream();
    $out= $this->newFixture($mem);
    $out->write('Hello');
    $out->close();
    Assert::equals('Hello', $mem->bytes());
  }

  #[Test]
  public function flushedOnDestruction() {
    $mem= new MemoryOutputStream();
    $out= $this->newFixture($mem);
    $out->write('Hello');
    unset($out);
    Assert::equals('Hello', $mem->bytes());
  }
}