<?php namespace net\xp_framework\unittest\io;

use io\File;
use io\Folder;
use lang\System;
use unittest\PrerequisitesNotMetError;

/**
 * TestCase
 *
 * @see      xp://io.File
 */
class FileIntegrationTest extends \unittest\TestCase {
  protected static $temp= null;
  protected $file= null;
  protected $folder= null;

  /**
   * Verifies TEMP directory is usable and there is enough space
   *
   * @return void
   */
  #[@beforeClass]
  public static function verifyTempDir() {
    self::$temp= System::tempDir();
    if (!is_writeable(self::$temp)) {
      throw new PrerequisitesNotMetError('$TEMP is not writeable', null, array(self::$temp.' +w'));
    }
    if (($df= disk_free_space(self::$temp)) < 10240) {
      throw new PrerequisitesNotMetError('Not enough space available in $TEMP', null, array(sprintf(
        'df %s = %.0fk > 10k',
        self::$temp,
        $df / 1024
      )));
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

  #[@test]
  public function doesNotExistYet() {
    $this->assertFalse($this->file->exists());
  }

  #[@test]
  public function existsAfterCreating() {
    $this->writeData($this->file, null);
    $this->assertTrue($this->file->exists());
  }

  #[@test]
  public function open_returns_file() {
    $this->assertEquals($this->file, $this->file->open(File::WRITE));
  }

  #[@test]
  public function noLongerExistsAfterDeleting() {
    $this->writeData($this->file, null);
    $this->file->unlink();
    $this->assertFalse($this->file->exists());
  }
  
  #[@test, @expect('io.IOException')]
  public function cannotDeleteNonExistant() {
    $this->file->unlink();
  }

  #[@test, @expect('lang.IllegalStateException')]
  public function cannotDeleteOpenFile() {
    $this->file->open(File::WRITE);
    $this->file->unlink();
  }

  #[@test, @expect('io.IOException')]
  public function cannotCloseUnopenedFile() {
    $this->file->close();
  }

  #[@test]
  public function write() {
    $this->assertEquals(5, $this->writeData($this->file, 'Hello'));
  }

  #[@test]
  public function read() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data, $this->file->read(strlen($data)));
      $this->file->close();
    }
  }

  #[@test]
  public function read0() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('', $this->file->read(0));
      $this->file->close();
    }
  }

  #[@test]
  public function readAfterEnd() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data, $this->file->read(strlen($data)));
      $this->assertFalse($this->file->read(1));
      $this->file->close();
    }
  }

  #[@test]
  public function gets() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data, $this->file->gets());
      $this->file->close();
    }
  }

  #[@test]
  public function gets0() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('', $this->file->gets(0));
      $this->file->close();
    }
  }

  #[@test]
  public function getsTwoLines() {
    with ($data= "Hello\nWorld\n"); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals("Hello\n", $this->file->gets());
      $this->assertEquals("World\n", $this->file->gets());
      $this->file->close();
    }
  }

  #[@test]
  public function getsAfterEnd() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('Hello', $this->file->gets());
      $this->assertFalse($this->file->gets());
      $this->file->close();
    }
  }

  #[@test]
  public function readLine() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data, $this->file->readLine());
      $this->file->close();
    }
  }

  #[@test]
  public function readLine0() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('', $this->file->readLine(0));
      $this->file->close();
    }
  }

  #[@test]
  public function readLines() {
    with ($data= "Hello\nWorld\n"); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('Hello', $this->file->readLine());
      $this->assertEquals('World', $this->file->readLine());
      $this->file->close();
    }
  }

  #[@test]
  public function readLinesAfterEnd() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('Hello', $this->file->readLine());
      $this->assertFalse($this->file->readLine());
      $this->file->close();
    }
  }

  #[@test]
  public function readChar() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data{0}, $this->file->readChar());
      $this->file->close();
    }
  }

  #[@test]
  public function readChars() {
    with ($data= 'Hello'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data{0}, $this->file->readChar());
      $this->assertEquals($data{1}, $this->file->readChar());
      $this->file->close();
    }
  }

  #[@test]
  public function readCharsAfterEnd() {
    with ($data= 'H'); {
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals('H', $this->file->readChar());
      $this->assertFalse($this->file->readChar());
      $this->file->close();
    }
  }

  #[@test]
  public function overwritingExistant() {
    with ($data= 'Hello World', $appear= 'This should not appear'); {
      $this->writeData($this->file, $appear);
      $this->writeData($this->file, $data);

      $this->file->open(File::READ);
      $this->assertEquals($data, $this->file->read(strlen($data)));
      $this->file->close();
    }
  }

  #[@test]
  public function appendingToExistant() {
    with ($data= 'Hello World', $appear= 'This should appear'); {
      $this->writeData($this->file, $appear);
      $this->writeData($this->file, $data, true);

      $this->assertEquals($appear.$data, $this->readData($this->file, strlen($appear) + strlen($data)));
    }
  }

  #[@test, @expect('io.FileNotFoundException')]
  public function cannotOpenNonExistantForReading() {
    $this->file->open(File::READ);
  }

  #[@test]
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

  #[@test]
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

  #[@test, @expect('lang.IllegalStateException')]
  public function cannotCopyOpenFile() {
    $this->file->open(File::WRITE);
    $this->file->copy('irrelevant');
  }

  #[@test]
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

  #[@test, @ignore('Breaks on Win2008 server, need special handling')]
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

  #[@test, @expect('lang.IllegalStateException')]
  public function cannotMoveOpenFile() {
    $this->file->open(File::WRITE);
    $this->file->move('irrelevant');
  }

  #[@test]
  public function copyingToAnotherFile() {
    $this->writeData($this->file, null);
    $target= new File($this->file->getURI().'.moved');
    $this->file->copy($target);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[@test]
  public function copyingToAnotherFolder() {
    $this->writeData($this->file, null);
    $target= new File($this->folder, $this->file->getFilename());
    $this->file->copy($this->folder);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[@test]
  public function movingToAnotherFile() {
    $this->writeData($this->file, null);
    $target= new File($this->file->getURI().'.moved');
    $this->file->move($target);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }

  #[@test]
  public function movingToAnotherFolder() {
    $this->writeData($this->file, null);
    $target= new File($this->folder, $this->file->getFilename());
    $this->file->move($this->folder);
    $exists= $target->exists();
    $target->unlink();
    $this->assertTrue($exists);
  }
}
