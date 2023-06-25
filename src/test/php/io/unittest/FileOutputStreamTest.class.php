<?php namespace io\unittest;

use io\streams\FileOutputStream;
use io\{File, Files, IOException, TempFile};
use lang\IllegalArgumentException;
use test\{Assert, Expect, Test};

class FileOutputStreamTest {
  private $files= [];

  /** @return io.TempFile */
  private function tempFile() {
    $file= (new TempFile())->containing('Created by FileOutputStreamTest');
    $this->files[]= $file;
    return $file;
  }

  #[After]
  public function cleanUp() {
    foreach ($this->files as $file) {
      try {
        $file->isOpen() && $file->close();
        $file->unlink();
      } catch (IOException $ignored) {
        // Can't really do anything about it...
      }
    }
  }

  #[Test]
  public function writing() {
    $file= $this->tempFile();
    with (new FileOutputStream($file), function($stream) use($file) {
      $buffer= 'Created by writing';
      $stream->write($buffer);
      $file->close();
      Assert::equals($buffer, Files::read($file));
    });
  }

  #[Test]
  public function appending() {
    $file= $this->tempFile();
    with (new FileOutputStream($file, true), function($stream) use($file) {
      $stream->write('!');
      $file->close();
      Assert::equals('Created by FileOutputStreamTest!', Files::read($file));
    });
  }

  #[Test]
  public function delete() {
    $file= $this->tempFile();
    with (new FileOutputStream($file), function($stream) use($file) {
      Assert::true($file->isOpen());
      unset($stream);
      Assert::true($file->isOpen());
    });
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function given_an_invalid_file_an_exception_is_raised() {
    new FileOutputStream('');
  }

  #[Test, Expect(IOException::class)]
  public function cannot_write_after_closing() {
    with (new FileOutputStream($this->tempFile()), function($stream) {
      $stream->close();
      $stream->write('');
    });
  }

  #[Test]
  public function calling_close_twice_has_no_effect() {
    with (new FileOutputStream($this->tempFile()), function($stream) {
      $stream->close();
      $stream->close();
    });
  }

  #[Test]
  public function tell_initially() {
    $stream= new FileOutputStream($this->tempFile());
    Assert::equals(0, $stream->tell());
  }

  #[Test]
  public function tell_after_writing() {
    $stream= new FileOutputStream($this->tempFile());
    $stream->write('Test');
    Assert::equals(4, $stream->tell());
  }

  #[Test]
  public function tell_after_seeking() {
    $stream= new FileOutputStream($this->tempFile());
    $stream->write('Test');
    $stream->seek(0, SEEK_SET);
    Assert::equals(0, $stream->tell());
  }

  #[Test]
  public function truncation() {
    $file= $this->tempFile();
    $file->open(File::READWRITE);
    $file->write('Existing');

    with (new FileOutputStream($file), function($stream) use($file) {
      $stream->truncate(5);
      $file->close();
      Assert::equals('Exist', Files::read($file));
    });
  }
}