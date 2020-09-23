<?php namespace net\xp_framework\unittest\io;

use io\{File, FileNotFoundException, Folder, IOException};
use lang\{Environment, IllegalStateException};
use unittest\{BeforeClass, Expect, Ignore, PrerequisitesNotMetError, Test, TestCase};

class FileIntegrationTest extends TestCase {
  const TESTDATA = 'Test';

  protected static $temp= null;
  protected $file= null;
  protected $folder= null;

  /**
   * Verifies TEMP directory is usable and there is enough space
   *
   * @return void
   */
  #[BeforeClass]
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

  /**
   * Creates file fixture, ensures it doesn't exist before tests start 
   * running, then creates folder fixture, ensuring it exists and is
   * empty.
   *
   * @return void
   */
  public function setUp() {
    $unid= getmypid();
    $this->file= new File(self::$temp, '.xp-'.$unid.$this->getName().'file');
    if (file_exists($this->file->getURI())) {
      unlink($this->file->getURI());
    }

    $this->folder= new Folder($this->file->getPath(), '.xp-'.$unid.$this->getName().'folder');
    if (!file_exists($this->folder->getURI())) {
      mkdir($this->folder->getURI());
    } else {
      foreach (scandir($this->folder->getURI()) as $file) {
        if ('.' === $file || '..' === $file) continue;
        unlink($this->folder->getURI().$file);
      }
    }
  }
  
  /**
   * Deletes file and folder fixtures.
   *
   * @return void
   */
  public function tearDown() {
    $this->file->isOpen() && $this->file->close();

    if (file_exists($this->file->getURI())) {
      unlink($this->file->getURI());
    }

    if (file_exists($this->folder->getURI())) {
      foreach (scandir($this->folder->getURI()) as $file) {
        if ('.' === $file || '..' === $file) continue;
        unlink($this->folder->getURI().$file);
      }
      rmdir($this->folder->getURI());
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
    $this->assertFalse($this->file->exists());
  }

  #[Test]
  public function existsAfterCreating() {
    $this->writeData($this->file, null);
    $this->assertTrue($this->file->exists());
  }

  #[Test]
  public function open_returns_file() {
    $this->assertEquals($this->file, $this->file->open(File::WRITE));
  }

  #[Test]
  public function noLongerExistsAfterDeleting() {
    $this->writeData($this->file, null);
    $this->file->unlink();
    $this->assertFalse($this->file->exists());
  }
  
  #[Test, Expect(IOException::class)]
  public function cannotDeleteNonExistant() {
    $this->file->unlink();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotDeleteOpenFile() {
    $this->file->open(File::WRITE);
    $this->file->unlink();
  }

  #[Test, Expect(IOException::class)]
  public function cannotCloseUnopenedFile() {
    $this->file->close();
  }

  #[Test]
  public function write() {
    $this->assertEquals(4, $this->writeData($this->file, self::TESTDATA));
  }

  #[Test]
  public function read() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(self::TESTDATA, $this->file->read(strlen(self::TESTDATA)));
    $this->file->close();
  }

  #[Test]
  public function read0() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals('', $this->file->read(0));
    $this->file->close();
  }

  #[Test]
  public function readAfterEnd() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(self::TESTDATA, $this->file->read(strlen(self::TESTDATA)));
    $this->assertFalse($this->file->read(1));
    $this->file->close();
  }

  #[Test]
  public function gets() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(self::TESTDATA, $this->file->gets());
    $this->file->close();
  }

  #[Test]
  public function gets0() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals('', $this->file->gets(0));
    $this->file->close();
  }

  #[Test]
  public function getsTwoLines() {
    $this->writeData($this->file, "Hello\nWorld\n");

    $this->file->open(File::READ);
    $this->assertEquals("Hello\n", $this->file->gets());
    $this->assertEquals("World\n", $this->file->gets());
    $this->file->close();
  }

  #[Test]
  public function getsAfterEnd() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(self::TESTDATA, $this->file->gets());
    $this->assertFalse($this->file->gets());
    $this->file->close();
  }

  #[Test]
  public function readLine() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(self::TESTDATA, $this->file->readLine());
    $this->file->close();
  }

  #[Test]
  public function readLine0() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals('', $this->file->readLine(0));
    $this->file->close();
  }

  #[Test]
  public function readLines() {
    $this->writeData($this->file, "Hello\nWorld\n");

    $this->file->open(File::READ);
    $this->assertEquals('Hello', $this->file->readLine());
    $this->assertEquals('World', $this->file->readLine());
    $this->file->close();
  }

  #[Test]
  public function readLinesAfterEnd() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(self::TESTDATA, $this->file->readLine());
    $this->assertFalse($this->file->readLine());
    $this->file->close();
  }

  #[Test]
  public function readChar() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(substr(self::TESTDATA, 0, 1), $this->file->readChar());
    $this->file->close();
  }

  #[Test]
  public function readChars() {
    $this->writeData($this->file, self::TESTDATA);

    $this->file->open(File::READ);
    $this->assertEquals(substr(self::TESTDATA, 0, 1), $this->file->readChar());
    $this->assertEquals(substr(self::TESTDATA, 1, 1), $this->file->readChar());
    $this->file->close();
  }

  #[Test]
  public function readCharsAfterEnd() {
    $this->writeData($this->file, 'H');

    $this->file->open(File::READ);
    $this->assertEquals('H', $this->file->readChar());
    $this->assertFalse($this->file->readChar());
    $this->file->close();
  }

  #[Test]
  public function overwritingExistant() {
    with ($data= 'Hello World', $appear= 'This should not appear'); {
      $this->writeData($this->file, $appear);
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data, $this->file->read(strlen($data)));
      $this->file->close();
    }
  }

  #[Test]
  public function appendingToExistant() {
    with ($data= 'Hello World', $appear= 'This should appear'); {
      $this->writeData($this->file, $appear);
      $this->writeData($this->file, $data, true);

      $this->assertEquals($appear.$data, $this->readData($this->file, strlen($appear) + strlen($data)));
    }
  }

  #[Test, Expect(FileNotFoundException::class)]
  public function cannotOpenNonExistantForReading() {
    $this->file->open(File::READ);
  }

  #[Test]
  public function copying() {
    with ($data= 'Hello World'); {
      $this->writeData($this->file, $data);

      $copy= new File($this->file->getURI().'.copy');
      $this->file->copy($copy->getURI());

      $read= $this->readData($copy);
      $copy->unlink();
      
      $this->assertEquals($data, $read);
    }
  }

  #[Test]
  public function copyingOver() {
    with ($data= 'Hello World'); {
      $this->writeData($this->file, $data);

      $copy= new File($this->file->getURI().'.copy');
      $this->writeData($copy, 'Copy original content');
      $this->file->copy($copy->getURI());

      $read= $this->readData($copy);
      $copy->unlink();
      
      $this->assertEquals($data, $read);
    }
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotCopyOpenFile() {
    $this->file->open(File::WRITE);
    $this->file->copy('irrelevant');
  }

  #[Test]
  public function moving() {
    with ($data= 'Hello World'); {
      $this->writeData($this->file, $data);

      $target= new File($this->file->getURI().'.moved');
      $this->file->move($target->getURI());

      $read= $this->readData($target);
      $target->unlink();
      
      $this->assertEquals($data, $read);
      
      // FIXME I don't think io.File should be updating its URI when 
      // move() is called. Because it does, this assertion fails!
      // $this->assertFalse($this->file->exists()); 
    }
  }

  #[Test, Ignore('Breaks on Win2008 server, need special handling')]
  public function movingOver() {
    with ($data= 'Hello World'); {
      $this->writeData($this->file, $data);

      $target= new File($this->file->getURI().'.moved');
      $this->writeData($target, 'Target original content');
      $this->file->move($target->getURI());

      $read= $this->readData($target);
      $target->unlink();

      $this->assertEquals($data, $read);
      
      // FIXME I don't think io.File should be updating its URI when 
      // move() is called. Because it does, this assertion fails!
      // $this->assertFalse($this->file->exists()); 
    }
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotMoveOpenFile() {
    $this->file->open(File::WRITE);
    $this->file->move('irrelevant');
  }

  #[Test]
  public function copyingToAnotherFile() {
    $this->writeData($this->file, null);
    $target= new File($this->file->getURI().'.moved');
    $this->file->copy($target);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[Test]
  public function copyingToAnotherFolder() {
    $this->writeData($this->file, null);
    $target= new File($this->folder, $this->file->getFilename());
    $this->file->copy($this->folder);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[Test]
  public function movingToAnotherFile() {
    $this->writeData($this->file, null);
    $target= new File($this->file->getURI().'.moved');
    $this->file->move($target);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[Test]
  public function movingToAnotherFolder() {
    $this->writeData($this->file, null);
    $target= new File($this->folder, $this->file->getFilename());
    $this->file->move($this->folder);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[Test]
  public function truncate_to_zero() {
    $this->writeData($this->file, 'test');

    $this->file->open(File::READWRITE);
    $this->file->truncate();
    $this->file->close();

    $this->assertEquals(0, $this->file->size());
  }

  #[Test]
  public function shorten_file_using_truncate() {
    $this->writeData($this->file, 'test');

    $this->file->open(File::READWRITE);
    $this->file->truncate(3);
    $this->file->close();

    $this->assertEquals('tes', $this->readData($this->file));
  }

  #[Test]
  public function lengthen_file_using_truncate() {
    $this->writeData($this->file, 'test');

    $this->file->open(File::READWRITE);
    $this->file->truncate(5);
    $this->file->close();

    $this->assertEquals("test\x00", $this->readData($this->file));
  }

  #[Test]
  public function writing_after_truncate() {
    $this->writeData($this->file, 'test');

    $this->file->open(File::READWRITE);
    $this->file->truncate(4);
    $this->file->write('T');
    $this->file->close();

    $this->assertEquals('Test', $this->readData($this->file));
  }

  #[Test]
  public function writing_does_not_change_file_pointer() {
    $this->writeData($this->file, 'test');

    $this->file->open(File::READWRITE);
    $this->file->seek(2, SEEK_SET);
    $this->file->truncate(4);
    $this->file->write('S');
    $this->file->close();

    $this->assertEquals('teSt', $this->readData($this->file));
  }

  #[Test]
  public function writing_to_offset_larger_than_filesize() {
    $this->writeData($this->file, 'test');

    $this->file->open(File::READWRITE);
    $this->file->seek(0, SEEK_END);
    $this->file->truncate(2);
    $this->file->write('T');
    $this->file->close();

    $this->assertEquals("te\x00\x00T", $this->readData($this->file));
  }

  #[Test]
  public function tell_after_open() {
    $this->file->open(File::WRITE);
    $pos= $this->file->tell();
    $this->file->close();

    $this->assertEquals(0, $pos);
  }

  #[Test]
  public function tell_after_write() {
    $this->file->open(File::WRITE);
    $this->file->write('Test');
    $pos= $this->file->tell();
    $this->file->close();

    $this->assertEquals(4, $pos);
  }

  #[Test]
  public function tell_after_seek() {
    $this->file->open(File::WRITE);
    $this->file->write('Test');
    $this->file->seek(2, SEEK_SET);
    $pos= $this->file->tell();
    $this->file->close();

    $this->assertEquals(2, $pos);
  }

  #[Test]
  public function tell_after_seek_cur() {
    $this->file->open(File::WRITE);
    $this->file->write('Test');
    $this->file->seek(-2, SEEK_CUR);
    $pos= $this->file->tell();
    $this->file->close();

    $this->assertEquals(2, $pos);
  }

  #[Test]
  public function tell_after_seek_end() {
    $this->file->open(File::WRITE);
    $this->file->write('Test');
    $this->file->seek(-1, SEEK_END);
    $pos= $this->file->tell();
    $this->file->close();

    $this->assertEquals(3, $pos);
  }

  #[Test]
  public function seek_beyond_file_end() {
    $this->file->open(File::WRITE);
    $this->file->seek(4, SEEK_SET);
    $pos= $this->file->tell();
    $this->file->close();

    $this->assertEquals(4, $pos);
  }

  #[Test]
  public function writing_beyond_file_end_padds_with_zero() {
    $this->file->open(File::WRITE);
    $this->file->seek(2, SEEK_SET);
    $this->file->write('Test');
    $this->file->write('!');
    $this->file->close();

    $this->assertEquals("\x00\x00Test!", $this->readData($this->file));
  }
}