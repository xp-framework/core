<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\FileInputStream;
use io\{TempFile, IOException, FileNotFoundException};
use unittest\{TestCase, PrerequisitesNotMetError};

class FileInputStreamTest extends TestCase {
  private $file;

  /**
   * Sets up test case - creates temporary file
   *
   * @return void
   */
  public function setUp() {
    try {
      $this->file= (new TempFile())->containing('Created by FileInputStreamTest');
    } catch (IOException $e) {
      throw new PrerequisitesNotMetError('Cannot write temporary file', $e, [$this->file]);
    }
  }
  
  /**
   * Tear down this test case - removes temporary file
   *
   * @return void
   */
  public function tearDown() {
    try {
      $this->file->isOpen() && $this->file->close();
      $this->file->unlink();
    } catch (IOException $ignored) {
      // Can't really do anything about it...
    }
  }
  
  #[@test]
  public function reading() {
    with (new FileInputStream($this->file), function($stream) {
      $this->assertEquals('Created by ', $stream->read(11));
      $this->assertEquals('FileInputStreamTest', $stream->read());
      $this->assertEquals('', $stream->read());
    });
  }

  #[@test]
  public function seeking() {
    with (new FileInputStream($this->file), function($stream) {
      $this->assertEquals(0, $stream->tell());
      $stream->seek(20);
      $this->assertEquals(20, $stream->tell());
      $this->assertEquals('StreamTest', $stream->read());
    });
  }

  #[@test]
  public function availability() {
    with (new FileInputStream($this->file), function($stream) {
      $this->assertNotEquals(0, $stream->available());
      $stream->read(30);
      $this->assertEquals(0, $stream->available());
    });
  }

  #[@test]
  public function delete() {
    with (new FileInputStream($this->file), function($stream) {
      $this->assertTrue($this->file->isOpen());
      unset($stream);
      $this->assertTrue($this->file->isOpen());
    });
  }

  #[@test, @expect(FileNotFoundException::class)]
  public function nonExistantFile() {
    new FileInputStream('::NON-EXISTANT::');
  }

  #[@test, @expect(IOException::class)]
  public function readingAfterClose() {
    with (new FileInputStream($this->file), function($stream) {
      $stream->close();
      $stream->read();
    });
  }

  #[@test, @expect(IOException::class)]
  public function availableAfterClose() {
    with (new FileInputStream($this->file), function($stream) {
      $stream->close();
      $stream->available();
    });
  }

  #[@test]
  public function doubleClose() {
    with (new FileInputStream($this->file), function($stream) {
      $stream->close();
      $stream->close();
    });
  }
}
