<?php namespace io\unittest;

use io\streams\FileInputStream;
use io\{FileNotFoundException, IOException, TempFile};
use test\{After, Assert, Expect, PrerequisitesNotMetError, Test};

class FileInputStreamTest {
  private $files= [];

  /** @return io.TempFile */
  private function tempFile() {
    $file= (new TempFile())->containing('Created by FileInputStreamTest');
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
  public function reading() {
    with (new FileInputStream($this->tempFile()), function($stream) {
      Assert::equals('Created by ', $stream->read(11));
      Assert::equals('FileInputStreamTest', $stream->read());
      Assert::equals('', $stream->read());
    });
  }

  #[Test]
  public function seeking() {
    with (new FileInputStream($this->tempFile()), function($stream) {
      Assert::equals(0, $stream->tell());
      $stream->seek(20);
      Assert::equals(20, $stream->tell());
      Assert::equals('StreamTest', $stream->read());
    });
  }

  #[Test]
  public function availability() {
    with (new FileInputStream($this->tempFile()), function($stream) {
      Assert::notEquals(0, $stream->available());
      $stream->read(30);
      Assert::equals(0, $stream->available());
    });
  }

  #[Test]
  public function delete() {
    $file= $this->tempFile();
    with (new FileInputStream($file), function($stream) use($file) {
      Assert::true($file->isOpen());
      unset($stream);
      Assert::true($file->isOpen());
    });
  }

  #[Test, Expect(FileNotFoundException::class)]
  public function nonExistantFile() {
    new FileInputStream('::NON-EXISTANT::');
  }

  #[Test, Expect(IOException::class)]
  public function readingAfterClose() {
    with (new FileInputStream($this->tempFile()), function($stream) {
      $stream->close();
      $stream->read();
    });
  }

  #[Test, Expect(IOException::class)]
  public function availableAfterClose() {
    with (new FileInputStream($this->tempFile()), function($stream) {
      $stream->close();
      $stream->available();
    });
  }

  #[Test]
  public function doubleClose() {
    with (new FileInputStream($this->tempFile()), function($stream) {
      $stream->close();
      $stream->close();
    });
  }
}