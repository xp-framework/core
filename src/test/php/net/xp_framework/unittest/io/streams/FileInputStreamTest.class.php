<?php namespace net\xp_framework\unittest\io\streams;

use unittest\TestCase;
use io\streams\FileInputStream;
use io\FileUtil;
use io\TempFile;
use io\IOException;
use io\FileNotFoundException;
use unittest\PrerequisitesNotMetError;

class FileInputStreamTest extends TestCase {
  private $file;

  /**
   * Sets up test case - creates temporary file
   *
   * @return void
   */
  public function setUp() {
    try {
      $this->file= new TempFile();
      FileUtil::setContents($this->file, 'Created by FileInputStreamTest');
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
    with ($stream= new FileInputStream($this->file)); {
      $this->assertEquals('Created by ', $stream->read(11));
      $this->assertEquals('FileInputStreamTest', $stream->read());
      $this->assertEquals('', $stream->read());
    }
  }

  #[@test]
  public function seeking() {
    with ($stream= new FileInputStream($this->file)); {
      $this->assertEquals(0, $stream->tell());
      $stream->seek(20);
      $this->assertEquals(20, $stream->tell());
      $this->assertEquals('StreamTest', $stream->read());
    }
  }

  #[@test]
  public function availability() {
    with ($stream= new FileInputStream($this->file)); {
      $this->assertNotEquals(0, $stream->available());
      $stream->read(30);
      $this->assertEquals(0, $stream->available());
    }
  }

  #[@test]
  public function delete() {
    with ($stream= new FileInputStream($this->file)); {
      $this->assertTrue($this->file->isOpen());
      unset($stream);
      $this->assertTrue($this->file->isOpen());
    }
  }

  #[@test, @expect(FileNotFoundException::class)]
  public function nonExistantFile() {
    new FileInputStream('::NON-EXISTANT::');
  }

  #[@test, @expect(IOException::class)]
  public function readingAfterClose() {
    with ($stream= new FileInputStream($this->file)); {
      $stream->close();
      $stream->read();
    }
  }

  #[@test, @expect(IOException::class)]
  public function availableAfterClose() {
    with ($stream= new FileInputStream($this->file)); {
      $stream->close();
      $stream->available();
    }
  }

  #[@test]
  public function doubleClose() {
    with ($stream= new FileInputStream($this->file)); {
      $stream->close();
      $stream->close();
    }
  }
}
