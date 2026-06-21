<?php namespace io\unittest;

use io\streams\{SpooledInputStream, MemoryInputStream, Streams};
use io\{TempFile, Folder, Path, OperationFailed};
use lang\Environment;
use test\{Assert, Expect, Test, Values};

class SpooledInputStreamTest {
  const BYTES= 'Test success';

  /** @param ?string|io.Path|io.Folder|io.File $temp */
  private function newFixture($temp= null): SpooledInputStream {
    return new SpooledInputStream(new MemoryInputStream(self::BYTES), $temp);
  }

  #[Test]
  public function can_create() {
    $this->newFixture();
  }

  #[Test]
  public function reading() {
    $stream= $this->newFixture();

    Assert::equals(0, $stream->tell());
    Assert::equals(strlen(self::BYTES), $stream->available());
    Assert::equals(self::BYTES, Streams::readAll($stream));
  }

  #[Test]
  public function reading_after_seeking() {
    $stream= $this->newFixture();
    $stream->seek(5);
    $stream->seek(0);

    Assert::equals(0, $stream->tell());
    Assert::equals(strlen(self::BYTES), $stream->available());
    Assert::equals(self::BYTES, Streams::readAll($stream));
  }

  #[Test, Values([0, 1, 5])]
  public function seeking_forward($offset) {
    $stream= $this->newFixture();
    $stream->seek($offset, SEEK_SET);

    Assert::equals($offset, $stream->tell());
    Assert::equals(strlen(self::BYTES) - $offset, $stream->available());
    Assert::equals(substr(self::BYTES, $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, -1, -5])]
  public function seeking_to_end($offset) {
    $stream= $this->newFixture();
    $stream->seek($offset, SEEK_END);

    Assert::equals(strlen(self::BYTES) + $offset, $stream->tell());
    Assert::equals(-$offset, $stream->available());
    Assert::equals(substr(self::BYTES, strlen(self::BYTES) + $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, 1, -1])]
  public function seeking_relative($offset) {
    $stream= $this->newFixture();
    $stream->seek(5, SEEK_SET);
    $stream->seek($offset, SEEK_CUR);

    Assert::equals(5 + $offset, $stream->tell());
    Assert::equals(strlen(self::BYTES) - 5 - $offset, $stream->available());
    Assert::equals(substr(self::BYTES, 5 + $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, 1, 5])]
  public function seeking_forward_set($offset) {
    $stream= $this->newFixture();
    $stream->seek($offset, SEEK_SET);

    Assert::equals($offset, $stream->tell());
    Assert::equals(strlen(self::BYTES) - $offset, $stream->available());
    Assert::equals(substr(self::BYTES, $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, 1, 5])]
  public function seeking_forward_cur($offset) {
    $stream= $this->newFixture();
    $stream->seek(strlen(self::BYTES), SEEK_SET);
    $stream->seek(-$offset, SEEK_CUR);

    Assert::equals(strlen(self::BYTES) - $offset, $stream->tell());
    Assert::equals($offset, $stream->available());
    Assert::equals(substr(self::BYTES, strlen(self::BYTES) - $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, 1, 5])]
  public function seeking_forward_triggers_read($offset) {
    $stream= $this->newFixture();
    $stream->seek(5, SEEK_SET);
    $stream->seek(strlen(self::BYTES) - $offset, SEEK_SET);

    Assert::equals(strlen(self::BYTES) - $offset, $stream->tell());
    Assert::equals($offset, $stream->available());
    Assert::equals(substr(self::BYTES, strlen(self::BYTES) - $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, 1, 5])]
  public function seeking_back_to_offset_after_reading_until_end($offset) {
    $stream= $this->newFixture();
    while ($stream->available()) {
      $stream->read();
    }
    $stream->seek($offset, SEEK_SET);

    Assert::equals($offset, $stream->tell());
    Assert::equals(strlen(self::BYTES) - $offset, $stream->available());
    Assert::equals(substr(self::BYTES, $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, -1, -5])]
  public function seeking_to_end_after_reading_until_end($offset) {
    $stream= $this->newFixture();
    while ($stream->available()) {
      $stream->read();
    }
    $stream->seek($offset, SEEK_END);

    Assert::equals(strlen(self::BYTES) + $offset, $stream->tell());
    Assert::equals(-$offset, $stream->available());
    Assert::equals(substr(self::BYTES, strlen(self::BYTES) + $offset), Streams::readAll($stream));
  }

  #[Test, Values([0, -1, -5])]
  public function seeking_to_end_after_seeking_relative($offset) {
    $stream= $this->newFixture();
    $stream->seek(5, SEEK_SET);
    $stream->seek($offset, SEEK_END);

    Assert::equals(strlen(self::BYTES) + $offset, $stream->tell());
    Assert::equals(-$offset, $stream->available());
    Assert::equals(substr(self::BYTES, strlen(self::BYTES) + $offset), Streams::readAll($stream));
  }

  #[Test]
  public function close_can_be_called_twice() {
    $stream= $this->newFixture();

    $stream->close();
    $stream->close();
  }

  #[Test, Expect(OperationFailed::class), Values([SEEK_SET, SEEK_CUR])]
  public function cannot_seek_before_beginning_of_file($whence) {
    $this->newFixture()->seek(-1, $whence);
  }

  #[Test, Expect(OperationFailed::class)]
  public function cannot_seek_with_invalid_whence() {
    $this->newFixture()->seek(0, 6100);
  }

  #[Test]
  public function position_after_seek_error() {
    $stream= $this->newFixture();
    $stream->read(4);

    Assert::throws(OperationFailed::class, fn() => $stream->seek(-1));
    Assert::equals(4, $stream->tell());
  }

  #[Test]
  public function position_after_seek_set_past_end() {
    $stream= $this->newFixture();
    $stream->seek(strlen(self::BYTES) + 1, SEEK_SET);

    Assert::equals(strlen(self::BYTES), $stream->tell());
  }

  #[Test]
  public function position_after_seek_end_past_end() {
    $stream= $this->newFixture();
    $stream->seek(1, SEEK_END);

    Assert::equals(strlen(self::BYTES), $stream->tell());
  }
}