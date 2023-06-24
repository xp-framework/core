<?php namespace net\xp_framework\unittest\io;

use io\{File, FileNotFoundException, Folder, IOException};
use lang\{Environment, IllegalStateException};
use unittest\{Assert, After, Before, Expect, Ignore, Test};

class FileIntegrationTest {
  const TESTDATA= 'Test';

  private $files= [], $folders= [];
  private static $temp;

  #[Before]
  public static function verifyTempDir() {
    self::$temp= Environment::tempDir();
    if (!is_writeable(self::$temp)) {
      throw new PrerequisitesNotMetError('$TEMP is not writeable', null, [self::$temp.' +w']);
    }
    if (($df= disk_free_space(self::$temp)) < 10240) {
      throw new PrerequisitesNotMetError('Not enough space available in $TEMP', null, [sprintf(
        'df %s = %.0fk > 10k',
        self::$temp,
        $df / 1024
      )]);
    }
  }

  /** @return io.File */
  private function tempFile() {
    $file= new File(self::$temp, '.xp-'.uniqid().'-integration-file');
    $this->files[]= $file;
    return $file;
  }

  /** @return io.Folder */
  private function tempFolder() {
    $folder= new Folder(self::$temp, '.xp-'.uniqid().'-integration-folder');
    $this->folders[]= $folder;
    return $folder;
  }

  #[After]
  public function cleanUp() {
    foreach ($this->files as $file) {
      $file->isOpen() && $file->close();
      $file->exists() && $file->unlink();
    }
    foreach ($this->folders as $folder) {
      $folder->exists() && $folder->unlink();
    }
  }
 
  /**
   * Fill a given file with data - that is, open it in write mode,
   * write the data if not NULL, then finally close it.
   *
   * @param   io.File file
   * @param   string data default NULL
   * @param   bool append default FALSE
   * @return  int number of written bytes or 0 if NULL data was given
   * @throws  io.IOException
   */
  protected function writeData($file, $data= null, $append= false) {
    $file->open($append ? File::APPEND : File::WRITE);
    if (null === $data) {
      $written= 0;
    } else {
      $written= $file->write($data);
    }
    $file->close();
    return $written;
  }

  /**
   * Read data from a file - that is, open it in read mode, read
   * the number of bytes specified (or the entire file, if omitted),
   * then finally close it.
   *
   * @param   io.File file
   * @param   int length default -1
   * @return  string
   */
  protected function readData($file, $length= -1) {
    $file->open(File::READ);
    $data= $file->read($length < 0 ? $file->size() : $length);
    $file->close();
    return $data;
  }

  #[Test]
  public function doesNotExistYet() {
    Assert::false($this->tempFile()->exists());
  }

  #[Test]
  public function existsAfterCreating() {
    $file= $this->tempFile();
    $this->writeData($file, null);
    Assert::true($file->exists());
  }

  #[Test]
  public function open_returns_file() {
    $file= $this->tempFile();
    Assert::equals($file, $file->open(File::WRITE));
  }

  #[Test]
  public function noLongerExistsAfterDeleting() {
    $file= $this->tempFile();
    $this->writeData($file, null);
    $file->unlink();
    Assert::false($file->exists());
  }
  
  #[Test, Expect(IOException::class)]
  public function cannotDeleteNonExistant() {
    $this->tempFile()->unlink();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotDeleteOpenFile() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->unlink();
  }

  #[Test, Expect(IOException::class)]
  public function cannotCloseUnopenedFile() {
    $this->tempFile()->close();
  }

  #[Test]
  public function write() {
    Assert::equals(4, $this->writeData($this->tempFile(), self::TESTDATA));
  }

  #[Test]
  public function read() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(self::TESTDATA, $file->read(strlen(self::TESTDATA)));
  }

  #[Test]
  public function read0() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals('', $file->read(0));
  }

  #[Test]
  public function readAfterEnd() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(self::TESTDATA, $file->read(strlen(self::TESTDATA)));
    Assert::false($file->read(1));
  }

  #[Test]
  public function gets() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(self::TESTDATA, $file->gets());
  }

  #[Test]
  public function gets0() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals('', $file->gets(0));
  }

  #[Test]
  public function getsTwoLines() {
    $file= $this->tempFile();
    $this->writeData($file, "Hello\nWorld\n");

    $file->open(File::READ);
    Assert::equals("Hello\n", $file->gets());
    Assert::equals("World\n", $file->gets());
  }

  #[Test]
  public function getsAfterEnd() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(self::TESTDATA, $file->gets());
    Assert::false($file->gets());
  }

  #[Test]
  public function readLine() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(self::TESTDATA, $file->readLine());
  }

  #[Test]
  public function readLine0() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals('', $file->readLine(0));
  }

  #[Test]
  public function readLines() {
    $file= $this->tempFile();
    $this->writeData($file, "Hello\nWorld\n");

    $file->open(File::READ);
    Assert::equals('Hello', $file->readLine());
    Assert::equals('World', $file->readLine());
  }

  #[Test]
  public function readLinesAfterEnd() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(self::TESTDATA, $file->readLine());
    Assert::false($file->readLine());
  }

  #[Test]
  public function readChar() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(substr(self::TESTDATA, 0, 1), $file->readChar());
  }

  #[Test]
  public function readChars() {
    $file= $this->tempFile();
    $this->writeData($file, self::TESTDATA);

    $file->open(File::READ);
    Assert::equals(substr(self::TESTDATA, 0, 1), $file->readChar());
    Assert::equals(substr(self::TESTDATA, 1, 1), $file->readChar());
  }

  #[Test]
  public function readCharsAfterEnd() {
    $file= $this->tempFile();
    $this->writeData($file, 'H');

    $file->open(File::READ);
    Assert::equals('H', $file->readChar());
    Assert::false($file->readChar());
  }

  #[Test]
  public function overwritingExistant() {
    $file= $this->tempFile();
    with ($data= 'Hello World', $appear= 'This should not appear'); {
      $this->writeData($file, $appear);
      $this->writeData($file, $data);

      $file->open(File::READ);
      Assert::equals($data, $file->read(strlen($data)));
    }
  }

  #[Test]
  public function appendingToExistant() {
    $file= $this->tempFile();
    with ($data= 'Hello World', $appear= 'This should appear'); {
      $this->writeData($file, $appear);
      $this->writeData($file, $data, true);

      Assert::equals($appear.$data, $this->readData($file, strlen($appear) + strlen($data)));
    }
  }

  #[Test, Expect(FileNotFoundException::class)]
  public function cannotOpenNonExistantForReading() {
    $this->tempFile()->open(File::READ);
  }

  #[Test]
  public function copying() {
    $file= $this->tempFile();
    with ($data= 'Hello World'); {
      $this->writeData($file, $data);

      $copy= new File($file->getURI().'.copy');
      $file->copy($copy->getURI());

      $read= $this->readData($copy);
      $copy->unlink();
      
      Assert::equals($data, $read);
    }
  }

  #[Test]
  public function copyingOver() {
    $file= $this->tempFile();
    with ($data= 'Hello World'); {
      $this->writeData($file, $data);

      $copy= new File($file->getURI().'.copy');
      $this->writeData($copy, 'Copy original content');
      $file->copy($copy->getURI());

      $read= $this->readData($copy);
      $copy->unlink();
      
      Assert::equals($data, $read);
    }
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotCopyOpenFile() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->copy('irrelevant');
  }

  #[Test]
  public function moving() {
    $file= $this->tempFile();
    with ($data= 'Hello World'); {
      $this->writeData($file, $data);

      $target= new File($file->getURI().'.moved');
      $file->move($target->getURI());

      $read= $this->readData($target);
      $target->unlink();
      
      Assert::equals($data, $read);
    }
  }

  #[Test]
  public function movingOver() {
    $file= $this->tempFile();
    with ($data= 'Hello World'); {
      $this->writeData($file, $data);

      $target= new File($file->getURI().'.moved');
      $this->writeData($target, 'Target original content');
      $file->move($target->getURI());

      $read= $this->readData($target);
      $target->unlink();

      Assert::equals($data, $read);
    }
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotMoveOpenFile() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->move('irrelevant');
  }

  #[Test]
  public function copyingToAnotherFile() {
    $file= $this->tempFile();
    $this->writeData($file, null);
    $target= new File($file->getURI().'.copy');
    $file->copy($target);

    Assert::true($target->exists());
  }

  #[Test]
  public function copyingToAnotherFolder() {
    $file= $this->tempFile();
    $this->writeData($file, null);
    $folder= $this->tempFolder();
    $folder->create();
    $file->copy($folder);

    $target= new File($folder, $file->getFilename());
    Assert::true($target->exists());
  }

  #[Test]
  public function movingToAnotherFile() {
    $file= $this->tempFile();
    $this->writeData($file, null);
    $target= new File($file->getURI().'.moved');
    $file->move($target);

    Assert::true($target->exists());
  }

  #[Test]
  public function movingToAnotherFolder() {
    $file= $this->tempFile();
    $this->writeData($file, null);
    $folder= $this->tempFolder();
    $folder->create();
    $file->move($folder);

    $target= new File($folder, $file->getFilename());
    Assert::true($target->exists());
  }

  #[Test]
  public function truncate_to_zero() {
    $file= $this->tempFile();
    $this->writeData($file, 'test');

    $file->open(File::READWRITE);
    $file->truncate();
    $file->close();

    Assert::equals(0, $file->size());
  }

  #[Test]
  public function shorten_file_using_truncate() {
    $file= $this->tempFile();
    $this->writeData($file, 'test');

    $file->open(File::READWRITE);
    $file->truncate(3);
    $file->close();

    Assert::equals('tes', $this->readData($file));
  }

  #[Test]
  public function lengthen_file_using_truncate() {
    $file= $this->tempFile();
    $this->writeData($file, 'test');

    $file->open(File::READWRITE);
    $file->truncate(5);
    $file->close();

    Assert::equals("test\x00", $this->readData($file));
  }

  #[Test]
  public function writing_after_truncate() {
    $file= $this->tempFile();
    $this->writeData($file, 'test');

    $file->open(File::READWRITE);
    $file->truncate(4);
    $file->write('T');
    $file->close();

    Assert::equals('Test', $this->readData($file));
  }

  #[Test]
  public function writing_does_not_change_file_pointer() {
    $file= $this->tempFile();
    $this->writeData($file, 'test');

    $file->open(File::READWRITE);
    $file->seek(2, SEEK_SET);
    $file->truncate(4);
    $file->write('S');
    $file->close();

    Assert::equals('teSt', $this->readData($file));
  }

  #[Test]
  public function writing_to_offset_larger_than_filesize() {
    $file= $this->tempFile();
    $this->writeData($file, 'test');

    $file->open(File::READWRITE);
    $file->seek(0, SEEK_END);
    $file->truncate(2);
    $file->write('T');
    $file->close();

    Assert::equals("te\x00\x00T", $this->readData($file));
  }

  #[Test]
  public function tell_after_open() {
    $file= $this->tempFile();
    $file->open(File::WRITE);

    Assert::equals(0, $file->tell());
  }

  #[Test]
  public function tell_after_write() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->write('Test');

    Assert::equals(4, $file->tell());
  }

  #[Test]
  public function tell_after_seek() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->write('Test');
    $file->seek(2, SEEK_SET);

    Assert::equals(2, $file->tell());
  }

  #[Test]
  public function tell_after_seek_cur() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->write('Test');
    $file->seek(-2, SEEK_CUR);

    Assert::equals(2, $file->tell());
  }

  #[Test]
  public function tell_after_seek_end() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->write('Test');
    $file->seek(-1, SEEK_END);

    Assert::equals(3, $file->tell());
  }

  #[Test]
  public function seek_beyond_file_end() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->seek(4, SEEK_SET);

    Assert::equals(4, $file->tell());
  }

  #[Test]
  public function writing_beyond_file_end_padds_with_zero() {
    $file= $this->tempFile();
    $file->open(File::WRITE);
    $file->seek(2, SEEK_SET);
    $file->write('Test');
    $file->write('!');

    Assert::equals("\x00\x00Test!", $this->readData($file));
  }
}