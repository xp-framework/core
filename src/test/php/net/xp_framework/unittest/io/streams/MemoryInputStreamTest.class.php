<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\MemoryInputStream;
use unittest\{Assert, Test};

class MemoryInputStreamTest {
  const BUFFER= 'Hello World, how are you doing?';

  /** @return io.streams.MemoryInputStream */
  private function newFixture() { return new MemoryInputStream(self::BUFFER); }

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
    Assert::equals(strlen(self::BUFFER) - 5, $in->available());
  }
  
  #[Test]
  public function closingTwice() {
    $in= $this->newFixture();
    $in->close();
    $in->close();
  }
}