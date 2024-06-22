<?php namespace io\unittest;

use io\File;
use io\streams\Buffer;
use lang\{Environment, IllegalArgumentException};
use test\{Assert, Before, Expect, Test, Values};

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

  #[Test, Expect(IllegalArgumentException::class)]
  public function threshold_must_be_larger_than_zero() {
    new Buffer($this->temp, -1);
  }

  #[Test, Values([0, 1, 127, 128])]
  public function uses_memory_under_threshold($length) {
    $bytes= str_repeat('*', $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);
    $fixture->finish();

    Assert::null($fixture->file());
    Assert::equals($length, $fixture->size());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([129, 256, 1024])]
  public function stores_file_when_exceeding_threshold($length) {
    $bytes= str_repeat('*', $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);
    $fixture->finish();

    Assert::instance(File::class, $fixture->file());
    Assert::equals($length, $fixture->size());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([127, 128, 129])]
  public function read_after_eof($length) {
    $bytes= str_repeat('*', self::THRESHOLD + $length);

    $fixture= new Buffer($this->temp, self::THRESHOLD);
    $fixture->write($bytes);
    $fixture->finish();

    Assert::equals($bytes, $fixture->read());
    Assert::equals(0, $fixture->available());
    Assert::equals(null, $fixture->read());
  }

  #[Test]
  public function file_deleted_on_close() {
    $fixture= new Buffer($this->temp, 0);
    $fixture->write('Test');
    $fixture->finish();

    $fixture->close();
    Assert::false($fixture->file()->exists());
  }

  #[Test]
  public function double_close() {
    $fixture= new Buffer($this->temp, 0);
    $fixture->write('Test');
    $fixture->finish();

    $fixture->close();
    $fixture->close();
  }
}