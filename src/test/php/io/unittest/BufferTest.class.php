<?php namespace io\unittest;

use io\streams\Buffer;
use io\{File, TempFile, Folder, Path, Blob, OperationFailed};
use lang\{Environment, IllegalArgumentException};
use test\{Assert, Test, Values};

class BufferTest {
  const THRESHOLD= 128;

  /** Creates a new fixture */
  private function newFixture(int $threshold= self::THRESHOLD, bool $persist= false): Buffer {
    return new Buffer(Environment::tempDir(), $threshold, $persist);
  }

  /** @return iterable */
  private function directories() {
    $temp= Environment::tempDir();

    yield [$temp];
    yield [new Path($temp)];
    yield [new Folder($temp)];
  }

  #[Test, Values(from: 'directories')]
  public function with_directory($files) {
    new Buffer($files, self::THRESHOLD);
  }

  #[Test]
  public function with_temp_file() {
    $t= new TempFile();
    $fixture= new Buffer($t);
    $fixture->write('Test');

    Assert::equals($t, $fixture->file());
  }

  #[Test]
  public function with_temp_path() {
    $t= new TempFile();
    $fixture= new Buffer(new Path($t));
    $fixture->write('Test');

    Assert::equals($t, $fixture->file());
  }

  #[Test]
  public function threshold_must_be_larger_than_zero() {
    Assert::throws(IllegalArgumentException::class, fn() => $this->newFixture(-1));
  }

  #[Test, Values([1, 127, 128])]
  public function uses_memory_under_threshold($length) {
    $bytes= str_repeat('*', $length);

    $fixture= $this->newFixture();
    $fixture->write($bytes);
    $fixture->reset();

    Assert::null($fixture->file());
    Assert::equals($length, $fixture->size());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([129, 256, 1024])]
  public function stores_file_when_exceeding_threshold($length) {
    $bytes= str_repeat('*', $length);

    $fixture= $this->newFixture();
    $fixture->write($bytes);
    $fixture->reset();

    Assert::instance(File::class, $fixture->file());
    Assert::equals($length, $fixture->size());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([127, 128, 129])]
  public function read_after_eof($length) {
    $bytes= str_repeat('*', $length);

    $fixture= $this->newFixture();
    $fixture->write($bytes);
    $fixture->reset();

    Assert::equals($length, $fixture->available());
    Assert::equals($bytes, $fixture->read());
    Assert::equals(0, $fixture->available());
    Assert::equals('', $fixture->read());
  }

  #[Test, Values([127, 128, 129])]
  public function reset($length) {
    $bytes= str_repeat('*', $length);

    $fixture= $this->newFixture();
    $fixture->write($bytes);

    // At EOF, there is nothing to read
    Assert::equals(0, $fixture->available());
    Assert::equals('', $fixture->read());

    $fixture->reset();

    // Back at the beginning of the file
    Assert::equals($length, $fixture->available());
    Assert::equals($bytes, $fixture->read());
  }

  #[Test, Values([3, 4, 5, 6, 7])]
  public function write_after_read_with($threshold) {
    $fixture= $this->newFixture($threshold);

    $fixture->write('Test');
    $fixture->seek(-4, SEEK_CUR);
    Assert::equals('Test', $fixture->read());

    $fixture->write('ed');
    $fixture->seek(-2, SEEK_CUR);
    Assert::equals('ed', $fixture->read());
  }

  #[Test]
  public function file_created_on_write() {
    $fixture= $this->newFixture(0);
    $fixture->write('Test');

    Assert::true($fixture->file()->exists());
  }

  #[Test]
  public function file_deleted_on_close() {
    $fixture= $this->newFixture(0);
    $fixture->write('Test');
    Assert::true($fixture->file()->exists());

    $fixture->close();
    Assert::false($fixture->file()->exists());
  }

  #[Test]
  public function file_kept_on_close_with_persist() {
    $fixture= $this->newFixture(0, true);
    $fixture->write('Test');
    Assert::true($fixture->file()->exists());

    $fixture->close();
    Assert::true($fixture->file()->exists());
  }

  #[Test, Values([0, 128])]
  public function tell_after_write_with($threshold) {
    $fixture= $this->newFixture($threshold);
    $fixture->write('Test success');

    Assert::equals(12, $fixture->tell());
    Assert::equals('', $fixture->read());
  }

  #[Test, Values([0, 128])]
  public function tell_after_read_with($threshold) {
    $fixture= $this->newFixture($threshold);
    $fixture->write('Test success');
    $fixture->reset();
    $fixture->read(5);

    Assert::equals(5, $fixture->tell());
    Assert::equals('success', $fixture->read());
  }

  #[Test, Values([0, 128])]
  public function seek_set_with($threshold) {
    $fixture= $this->newFixture($threshold);
    $fixture->write('Test success');
    $fixture->seek(5, SEEK_SET);

    Assert::equals(5, $fixture->tell());
    Assert::equals('success', $fixture->read());
  }

  #[Test, Values([0, 128])]
  public function seek_cur_with($threshold) {
    $fixture= $this->newFixture($threshold);
    $fixture->write('Test success');
    $fixture->seek(-7, SEEK_CUR);

    Assert::equals(5, $fixture->tell());
    Assert::equals('success', $fixture->read());
  }

  #[Test, Values([0, 128])]
  public function seek_end_with($threshold) {
    $fixture= $this->newFixture($threshold);
    $fixture->write('Test success');

    $fixture->seek(-7, SEEK_END);
    Assert::equals(5, $fixture->tell());
    Assert::equals('success', $fixture->read());
  }

  #[Test]
  public function cannot_seek_before_start() {
    Assert::throws(OperationFailed::class, fn() => $this->newFixture()->seek(-1));
  }

  #[Test]
  public function cannot_seek_invalid_whence() {
    Assert::throws(OperationFailed::class, fn() => $this->newFixture()->seek(0, 6100));
  }

  #[Test]
  public function double_close() {
    $fixture= $this->newFixture(0);
    $fixture->write('Test');

    $fixture->close();
    $fixture->close();
  }

  #[Test]
  public function integrated_with_blob_api() {
    $fixture= $this->newFixture();
    $fixture->write('Test');
    $fixture->reset();

    $blob= new Blob($fixture);
    Assert::equals('Test', (string)$blob->bytes());
    Assert::equals('Test', (string)$blob->bytes());
  }
}