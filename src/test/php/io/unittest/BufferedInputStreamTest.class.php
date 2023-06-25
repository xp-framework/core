<?php namespace io\unittest;

use io\streams\{BufferedInputStream, MemoryInputStream};
use unittest\{Assert, Test, Values};

class BufferedInputStreamTest {
  const BUFFER= 'Hello World, how are you doing?';

  /** @return io.streams.BufferedInputStream */
  private function newFixture() { return new BufferedInputStream(new MemoryInputStream(self::BUFFER), 10); }

  #[Test]
  public function readAll() {
    $in= $this->newFixture();
    Assert::equals(self::BUFFER, $in->read(strlen(self::BUFFER)));
    Assert::equals(0, $in->available());
  }

  #[Test]
  public function readChunk() {
    $in= $this->newFixture();
    Assert::equals('Hello', $in->read(5));
    Assert::equals(5, $in->available());      // Five buffered bytes
  }
  
  #[Test]
  public function readChunks() {
    $in= $this->newFixture();
    Assert::equals('Hello', $in->read(5));
    Assert::equals(5, $in->available());      // Five buffered bytes
    Assert::equals(' Worl', $in->read(5));
    Assert::notEquals(0, $in->available());   // Buffer completely empty, but underlying stream has bytes
  }

  #[Test]
  public function closingTwiceHasNoEffect() {
    $in= $this->newFixture();
    $in->close();
    $in->close();
  }

  #[Test]
  public function readSize() {
    $in= $this->newFixture();
    Assert::equals('Hello Worl', $in->read(10));
    Assert::equals(strlen(self::BUFFER) - 10, $in->available());
  }

  #[Test, Values([1, 5, 10, 11])]
  public function pushBack($count) {
    $in= $this->newFixture();
    $chunk= $in->read($count);
    $in->pushBack($chunk);
    Assert::equals('Hello World', $in->read(11));
  }
}