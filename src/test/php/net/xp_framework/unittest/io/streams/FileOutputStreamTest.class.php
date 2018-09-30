<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\FileOutputStream;
use io\{File, FileUtil, TempFile, IOException};
use lang\IllegalArgumentException;
use unittest\PrerequisitesNotMetError;

class FileOutputStreamTest extends \unittest\TestCase {
  private $file;

  /**
   * Sets up test case - creates temporary file
   *
   * @return void
   */
  public function setUp() {
    try {
      $this->file= (new TempFile())->containing('Created by FileOutputStreamTest');
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
  public function writing() {
    with ($stream= new FileOutputStream($this->file), $buffer= 'Created by '.$this->name); {
      $stream->write($buffer);
      $this->file->close();
      $this->assertEquals($buffer, FileUtil::read($this->file));
    }
  }

  #[@test]
  public function appending() {
    with ($stream= new FileOutputStream($this->file, true)); {
      $stream->write('!');
      $this->file->close();
      $this->assertEquals('Created by FileOutputStreamTest!', FileUtil::read($this->file));
    }
  }

  #[@test]
  public function delete() {
    with ($stream= new FileOutputStream($this->file)); {
      $this->assertTrue($this->file->isOpen());
      unset($stream);
      $this->assertTrue($this->file->isOpen());
    }
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function given_an_invalid_file_an_exception_is_raised() {
    new FileOutputStream('');
  }

  #[@test, @expect(IOException::class)]
  public function cannot_write_after_closing() {
    with ($stream= new FileOutputStream($this->file)); {
      $stream->close();
      $stream->write('');
    }
  }

  #[@test]
  public function calling_close_twice_has_no_effect() {
    with ($stream= new FileOutputStream($this->file)); {
      $stream->close();
      $stream->close();
    }
  }

  #[@test]
  public function truncation() {
    FileUtil::write($this->file, 'Existing');

    with ($stream= new FileOutputStream($this->file, File::READWRITE)); {
      $stream->truncate(5);
      $this->file->close();
      $this->assertEquals('Exist', FileUtil::read($this->file));
    }
  }
}
