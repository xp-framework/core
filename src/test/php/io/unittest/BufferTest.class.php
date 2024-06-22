<?php namespace io\unittest;

use io\File;
use io\streams\Buffer;
use lang\{Environment, IllegalArgumentException, IllegalStateException};
use test\{Assert, Before, Test, Values};

class BufferTest {
  const THRESHOLD= 128;
  private $temp;

  #[Before]
  public function temp() {
    $this->temp= Environment::tempDir();
  }

  #[Test]
  public function can_create() {
    new Buffer($this->temp, self::THRESHOLD);
  }

  #[Test]
  public function threshold_must_be_larger_than_zero() {
    Assert::throws(IllegalArgumentException::class, fn() => new Buffer($this->temp, -1));
  }

  #[Test, Values([1, 127, 128])]
  public function uses_memory_under_threshold($length) {
    $bytes= str_repeat('*', $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);

    Assert::null($fixture->file());
    Assert::equals($length, $fixture->size());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([129, 256, 1024])]
  public function stores_file_when_exceeding_threshold($length) {
    $bytes= str_repeat('*', $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);

    Assert::instance(File::class, $fixture->file());
    Assert::equals($length, $fixture->size());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([127, 128, 129])]
  public function read_after_eof($length) {
    $bytes= str_repeat('*', $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);

    Assert::equals($length, $fixture->available());
    Assert::equals($bytes, $fixture->read());
    Assert::equals(0, $fixture->available());
    Assert::equals(null, $fixture->read());
  }

  #[Test, Values([127, 128, 129])]
  public function reset($length) {
    $bytes= str_repeat('*', $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);

    Assert::equals($length, $fixture->available());
    Assert::equals($bytes, $fixture->read());

    $fixture->reset();

    Assert::equals($length, $fixture->available());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test]
  public function cannot_write_after_draining_started() {
    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write('Test');
    Assert::false($fixture->draining());

    $fixture->read();
    Assert::true($fixture->draining());
    Assert::throws(IllegalStateException::class, fn() => $fixture->write('Test'));
  }

  #[Test]
  public function file_deleted_on_close() {
    $fixture= new Buffer($this->temp, 0);
    $fixture->write('Test');

    $fixture->close();
    Assert::false($fixture->file()->exists());
  }

  #[Test]
  public function double_close() {
    $fixture= new Buffer($this->temp, 0);
    $fixture->write('Test');

    $fixture->close();
    $fixture->close();
  }
}