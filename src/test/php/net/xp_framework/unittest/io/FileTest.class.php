<?php namespace net\xp_framework\unittest\io;

use unittest\TestCase;
use io\Folder;
use io\File;
use lang\Runtime;


/**
 * TestCase
 *
 * @see      xp://io.File
 */
class FileTest extends TestCase {

  /**
   * Return a file that is known to exist
   *
   * @return  string
   */
  protected function fileKnownToExist() {
    return realpath(Runtime::getInstance()->getExecutable()->getFilename());
  }

  /**
   * Test equals() method
   *
   */
  #[@test]
  public function sameInstanceIsEqual() {
    $f= new File($this->fileKnownToExist());
    $this->assertEquals($f, $f);
  }

  /**
   * Test equals() method
   *
   */
  #[@test]
  public function sameFileIsEqual() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals(new File($fn), new File($fn));
  }

  /**
   * Test equals() method
   *
   */
  #[@test]
  public function differentFilesAreNotEqual() {
    $this->assertNotEquals(new File($this->fileKnownToExist()), new File(__FILE__));
  }

  /**
   * Test hashCode() method
   *
   */
  #[@test]
  public function hashCodesNotEqualForTwoFileHandles() {
    $fn= $this->fileKnownToExist();
    $this->assertNotEquals(
      (new File(fopen($fn, 'r')))->hashCode(),
      (new File(fopen($fn, 'r')))->hashCode()
    );
  }

  /**
   * Test hashCode() method
   *
   */
  #[@test]
  public function hashCodesEqualForSameFileHandles() {
    $fn= fopen($this->fileKnownToExist(), 'r');
    $this->assertEquals(
      (new File($fn))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  /**
   * Test hashCode() method
   *
   */
  #[@test]
  public function hashCodesEqualForSameFiles() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals(
      (new File($fn))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  /**
   * Test hashCode() method
   *
   */
  #[@test]
  public function hashCodesNotEqualForHandleAndUri() {
    $fn= $this->fileKnownToExist();
    $this->assertNotEquals(
      (new File(fopen($fn, 'r')))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  /**
   * Test getURI() method
   *
   */
  #[@test]
  public function getURI() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File($fn))->getURI());
  }

  /**
   * Test getPath() method
   *
   * @see   php://pathinfo
   */
  #[@test]
  public function getPath() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['dirname'], (new File($fn))->getPath());
  }

  /**
   * Test getFileName() method
   *
   * @see   php://pathinfo
   */
  #[@test]
  public function getFileName() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['basename'], (new File($fn))->getFileName());
  }

  /**
   * Test getExtension() method
   *
   * @see   php://pathinfo
   */
  #[@test]
  public function getExtension() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals(
      isset($info['extension']) ? $info['extension'] : null, 
      (new File($fn))->getExtension()
    );
  }

  /**
   * Test NUL character is not allowed
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function nulCharacterNotAllowedInFilename() {
    new File("editor.txt\0.html");
  }

  /**
   * Test NUL character is not allowed
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function nulCharacterNotInTheBeginningOfFilename() {
    new File("\0editor.txt");
  }

  /**
   * Test creating a file with an empty filename
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function emptyFilenameNotAllowed() {
    new File('');
  }

  /**
   * Test creating a file with an empty filename
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function nullFilenameNotAllowed() {
    new File(null);
  }

  /**
   * Test composing filename by File("php://stdin")
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function filterScheme() {
    new File('php://filter/read=string.toupper|string.rot13/resource=http://www.example.comn');
  }

  /**
   * Test file object
   *
   */
  #[@test]
  public function newInstance() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File($fn))->getURI());
  }

  /**
   * Test composing filename by File(Folder, string)
   *
   */
  #[@test]
  public function composingFromFolderAndString() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File(new Folder(dirname($fn)), basename($fn)))->getURI());
  }

  /**
   * Test composing filename by File(Folder, string)
   *
   */
  #[@test]
  public function composingFromStringAndString() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File(dirname($fn), basename($fn)))->getURI());
  }

  /**
   * Test composing filename by File(resource)
   *
   */
  #[@test]
  public function fromResource() {
    $this->assertNull((new File(fopen($this->fileKnownToExist(), 'r')))->getURI());
  }

  /**
   * Test composing filename by File("php://stderr")
   *
   */
  #[@test]
  public function stderr() {
    $this->assertEquals('php://stderr', (new File('php://stderr'))->getURI());
  }

  /**
   * Test composing filename by File("php://stdout")
   *
   */
  #[@test]
  public function stdout() {
    $this->assertEquals('php://stdout', (new File('php://stdout'))->getURI());
  }

  /**
   * Test composing filename by File("php://stdin")
   *
   */
  #[@test]
  public function stdin() {
    $this->assertEquals('php://stdin', (new File('php://stdin'))->getURI());
  }

  /**
   * Test composing filename by File("xar://...")
   *
   */
  #[@test]
  public function xarSchemeAllowed() {
    $this->assertEquals('xar://test.xar?test.txt', (new File('xar://test.xar?test.txt'))->getURI());
  }

  /**
   * Test composing filename by File("res://...")
   *
   */
  #[@test]
  public function resSchemeAllowed() {
    $this->assertEquals('res://test.txt', (new File('res://test.txt'))->getURI());
  }

  /**
   * Test getFileName()
   *
   */
  #[@test]
  public function xarSchemeFileNameInRoot() {
    $this->assertEquals('test.txt', (new File('xar://test.xar?test.txt'))->getFileName());
  }

  /**
   * Test getPath()
   *
   */
  #[@test]
  public function xarSchemePathInRoot() {
    $this->assertEquals('xar://test.xar?', (new File('xar://test.xar?test.txt'))->getPath());
  }

  /**
   * Test getFileName()
   *
   */
  #[@test]
  public function xarSchemeFileNameInSubdir() {
    $this->assertEquals('test.txt', (new File('xar://test.xar?dir/test.txt'))->getFileName());
  }

  /**
   * Test getPath()
   *
   */
  #[@test]
  public function xarSchemePathInSubdir() {
    $this->assertEquals('xar://test.xar?dir', (new File('xar://test.xar?dir/test.txt'))->getPath());
  }

  /**
   * Test getFileName()
   *
   */
  #[@test]
  public function resSchemeFileName() {
    $this->assertEquals('test.txt', (new File('res://test.txt'))->getFileName());
  }

  /**
   * Test getPath()
   *
   */
  #[@test]
  public function resSchemePath() {
    $this->assertEquals('res://', (new File('res://test.txt'))->getPath());
  }

  /**
   * Test getPath()
   *
   */
  #[@test]
  public function resSchemePathInSubDir() {
    $this->assertEquals('res://dir', (new File('res://dir/test.txt'))->getPath());
  }

  /**
   * Test getPath()
   *
   */
  #[@test]
  public function resSchemeExtension() {
    $this->assertEquals('txt', (new File('res://test.txt'))->getExtension());
  }
}
