<?php namespace net\xp_framework\unittest\io;

use io\Folder;
use io\File;
use io\Path;
use io\streams\Streams;
use io\streams\MemoryInputStream;
use io\streams\MemoryOutputStream;
use lang\Runtime;
use lang\IllegalArgumentException;

/**
 * TestCase
 *
 * @see      xp://io.File
 */
class FileTest extends \unittest\TestCase {

  /**
   * Return a file that is known to exist
   *
   * @return  string
   */
  protected function fileKnownToExist() {
    return realpath(Runtime::getInstance()->getExecutable()->getFilename());
  }

  #[@test]
  public function sameInstanceIsEqual() {
    $f= new File($this->fileKnownToExist());
    $this->assertEquals($f, $f);
  }

  #[@test]
  public function sameFileIsEqual() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals(new File($fn), new File($fn));
  }

  #[@test]
  public function differentFilesAreNotEqual() {
    $this->assertNotEquals(new File($this->fileKnownToExist()), new File(__FILE__));
  }

  #[@test]
  public function hashCodesNotEqualForTwoFileHandles() {
    $fn= $this->fileKnownToExist();
    $a= new File(fopen($fn, 'r'));
    $b= new File(fopen($fn, 'r'));
    $this->assertNotEquals($a->hashCode(), $b->hashCode());
  }

  #[@test]
  public function hashCodesEqualForSameFileHandles() {
    $fn= fopen($this->fileKnownToExist(), 'r');
    $this->assertEquals(
      (new File($fn))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[@test]
  public function hashCodesEqualForSameFiles() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals(
      (new File($fn))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[@test]
  public function hashCodesNotEqualForHandleAndUri() {
    $fn= $this->fileKnownToExist();
    $this->assertNotEquals(
      (new File(fopen($fn, 'r')))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[@test]
  public function getURI() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File($fn))->getURI());
  }

  #[@test]
  public function getPath() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['dirname'], (new File($fn))->getPath());
  }

  #[@test]
  public function getFileName() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['basename'], (new File($fn))->getFileName());
  }

  #[@test]
  public function getExtension() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['extension'] ?? null, (new File($fn))->getExtension());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nulCharacterNotAllowedInFilename() {
    new File("editor.txt\0.html");
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nulCharacterNotInTheBeginningOfFilename() {
    new File("\0editor.txt");
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function emptyFilenameNotAllowed() {
    new File('');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nullFilenameNotAllowed() {
    new File(null);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function filterScheme() {
    new File('php://filter/read=string.toupper|string.rot13/resource=http://www.example.comn');
  }

  #[@test]
  public function newInstance() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File($fn))->getURI());
  }

  #[@test]
  public function composingFromFolderAndString() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File(new Folder(dirname($fn)), basename($fn)))->getURI());
  }

  #[@test]
  public function composingFromStringAndString() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File(dirname($fn), basename($fn)))->getURI());
  }

  #[@test]
  public function fromResource() {
    $this->assertNull((new File(fopen($this->fileKnownToExist(), 'r')))->getURI());
  }

  #[@test]
  public function stderr() {
    $this->assertEquals('php://stderr', (new File('php://stderr'))->getURI());
  }

  #[@test]
  public function stdout() {
    $this->assertEquals('php://stdout', (new File('php://stdout'))->getURI());
  }

  #[@test]
  public function stdin() {
    $this->assertEquals('php://stdin', (new File('php://stdin'))->getURI());
  }

  #[@test]
  public function xarSchemeAllowed() {
    $this->assertEquals('xar://test.xar?test.txt', (new File('xar://test.xar?test.txt'))->getURI());
  }

  #[@test]
  public function resSchemeAllowed() {
    $this->assertEquals('res://test.txt', (new File('res://test.txt'))->getURI());
  }

  #[@test]
  public function xarSchemeFileNameInRoot() {
    $this->assertEquals('test.txt', (new File('xar://test.xar?test.txt'))->getFileName());
  }

  #[@test]
  public function xarSchemePathInRoot() {
    $this->assertEquals('xar://test.xar?', (new File('xar://test.xar?test.txt'))->getPath());
  }

  #[@test]
  public function xarSchemeFileNameInSubdir() {
    $this->assertEquals('test.txt', (new File('xar://test.xar?dir/test.txt'))->getFileName());
  }

  #[@test]
  public function xarSchemePathInSubdir() {
    $this->assertEquals('xar://test.xar?dir', (new File('xar://test.xar?dir/test.txt'))->getPath());
  }

  #[@test]
  public function resSchemeFileName() {
    $this->assertEquals('test.txt', (new File('res://test.txt'))->getFileName());
  }

  #[@test]
  public function resSchemePath() {
    $this->assertEquals('res://', (new File('res://test.txt'))->getPath());
  }

  #[@test]
  public function resSchemePathInSubDir() {
    $this->assertEquals('res://dir', (new File('res://dir/test.txt'))->getPath());
  }

  #[@test]
  public function resSchemeExtension() {
    $this->assertEquals('txt', (new File('res://test.txt'))->getExtension());
  }

  #[@test]
  public function readable_stream() {
    $in= new MemoryInputStream('Test');
    $f= new File(Streams::readableUri($in));
    $f->open(File::READ);
    $bytes= $f->read($in->size());
    $f->close();

    $this->assertEquals('Test', $bytes);
  }

  #[@test]
  public function writeable_stream() {
    $out= new MemoryOutputStream();
    $f= new File(Streams::writeableUri($out));
    $f->open(File::READ);
    $f->write('Test');
    $f->close();

    $this->assertEquals('Test', $out->getBytes());
  }

  #[@test]
  public function pathClassCanBeUsedAsBase() {
    $fn= $this->fileKnownToExist();
    $f= new File(new Path(dirname($fn)), basename($fn));
    $this->assertEquals($fn, $f->getURI());
  }

  #[@test]
  public function pathClassCanBeUsedAsArg() {
    $fn= $this->fileKnownToExist();
    $f= new File(new Path($fn));
    $this->assertEquals($fn, $f->getURI());
  }

  #[@test]
  public function filesize() {
    $fn= $this->fileKnownToExist();
    $f= new File($fn);
    $this->assertEquals(filesize($fn), $f->size());
  }

  #[@test]
  public function filesize_of_fd() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    $this->assertEquals(4, $f->size());
  }
}
