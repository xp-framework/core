<?php namespace io\unittest;

use io\IOException;
use io\streams\MemoryInputStream;
use test\{Assert, Expect, Test, Values};

class MemoryInputStreamTest {
  const BUFFER= 'Hello World, how are you doing?';

  /** @return io.streams.MemoryInputStream */
  private function newFixture() { return new MemoryInputStream(self::BUFFER); }

  /** @return iterable */
  private function seeks() {

    // Start + offset
    yield [SEEK_SET, 1, 1];
    yield [SEEK_SET, 0, 0];

    // Current position + offset
    yield [SEEK_CUR, -1, 4];
    yield [SEEK_CUR, 0, 5];
    yield [SEEK_CUR, 1, 6];

    // End + offset
    yield [SEEK_END, -1, strlen(self::BUFFER) - 1];
    yield [SEEK_END, 0, strlen(self::BUFFER)];
    yield [SEEK_END, 1, strlen(self::BUFFER) + 1];
  }

  #[Test]
  public function size() {
    Assert::equals(strlen(self::BUFFER), $this->newFixture()->size());
  }

  #[Test]
  public function bytes() {
    Assert::equals(self::BUFFER, $this->newFixture()->bytes());
  }

  #[Test]
  public function initially_available() {
    Assert::equals(strlen(self::BUFFER), $this->newFixture()->available());
  }

  #[Test]
  public function read_all() {
    Assert::equals(self::BUFFER, $this->newFixture()->read(strlen(self::BUFFER)));
  }

  #[Test]
  public function read_chunk() {
    $in= $this->newFixture();
    Assert::equals('Hello', $this->newFixture()->read(5));
  }

  #[Test]
  public function available_after_reading() {
    $in= $this->newFixture();
    $in->read(5);
    Assert::equals(strlen(self::BUFFER) - 5, $in->available());
  }

  #[Test]
  public function tell() {
    Assert::equals(0, $this->newFixture()->tell());
  }

  #[Test]
  public function tell_after_reading() {
    $in= $this->newFixture();
    $in->read(5);
    Assert::equals(5, $in->tell());
  }

  #[Test]
  public function seek() {
    $in= $this->newFixture();
    $in->read(5);
    $in->seek(0);
    Assert::equals('Hello', $in->read(5));
  }

  #[Test, Expect(IOException::class)]
  public function seek_unknown_whence() {
    $in= $this->newFixture();
    $in->seek(0, 9999);
  }

  #[Test, Expect(IOException::class)]
  public function seek_before_start() {
    $in= $this->newFixture();
    $in->seek(0, -1);
  }

  #[Test, Values(from: 'seeks')]
  public function seeking_after_read($whence, $offset, $expected) {
    $in= $this->newFixture();
    $in->read(5);
    $in->seek($offset, $whence);
    Assert::equals($expected, $in->tell());
  }

  #[Test]
  public function closing_twice() {
    $in= $this->newFixture();
    $in->close();
    $in->close();
  }
}