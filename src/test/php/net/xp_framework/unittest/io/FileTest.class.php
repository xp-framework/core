<?php namespace net\xp_framework\unittest\io;

use io\streams\{MemoryInputStream, MemoryOutputStream, Streams};
use io\{File, Folder, Path};
use lang\{IllegalArgumentException, Runtime};
use unittest\{Expect, Test};

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

  #[Test]
  public function sameInstanceIsEqual() {
    $f= new File($this->fileKnownToExist());
    $this->assertEquals($f, $f);
  }

  #[Test]
  public function sameFileIsEqual() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals(new File($fn), new File($fn));
  }

  #[Test]
  public function differentFilesAreNotEqual() {
    $this->assertNotEquals(new File($this->fileKnownToExist()), new File(__FILE__));
  }

  #[Test]
  public function hashCodesNotEqualForTwoFileHandles() {
    $fn= $this->fileKnownToExist();
    $a= new File(fopen($fn, 'r'));
    $b= new File(fopen($fn, 'r'));
    $this->assertNotEquals($a->hashCode(), $b->hashCode());
  }

  #[Test]
  public function hashCodesEqualForSameFileHandles() {
    $handle= fopen($this->fileKnownToExist(), 'r');
    $a= new File($handle);
    $b= new File($handle);
    $this->assertEquals($a->hashCode(), $b->hashCode());
  }

  #[Test]
  public function hashCodesEqualForSameFiles() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals(
      (new File($fn))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[Test]
  public function hashCodesNotEqualForHandleAndUri() {
    $fn= $this->fileKnownToExist();
    $this->assertNotEquals(
      (new File(fopen($fn, 'r')))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[Test]
  public function getURI() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File($fn))->getURI());
  }

  #[Test]
  public function getPath() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['dirname'], (new File($fn))->getPath());
  }

  #[Test]
  public function getFileName() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['basename'], (new File($fn))->getFileName());
  }

  #[Test]
  public function getExtension() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    $this->assertEquals($info['extension'] ?? null, (new File($fn))->getExtension());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function nulCharacterNotAllowedInFilename() {
    new File("editor.txt\0.html");
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function nulCharacterNotInTheBeginningOfFilename() {
    new File("\0editor.txt");
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function emptyFilenameNotAllowed() {
    new File('');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function nullFilenameNotAllowed() {
    new File(null);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function filterScheme() {
    new File('php://filter/read=string.toupper|string.rot13/resource=http://www.example.comn');
  }

  #[Test]
  public function newInstance() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File($fn))->getURI());
  }

  #[Test]
  public function composingFromFolderAndString() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File(new Folder(dirname($fn)), basename($fn)))->getURI());
  }

  #[Test]
  public function composingFromStringAndString() {
    $fn= $this->fileKnownToExist();
    $this->assertEquals($fn, (new File(dirname($fn), basename($fn)))->getURI());
  }

  #[Test]
  public function fromResource() {
    $this->assertNull((new File(fopen($this->fileKnownToExist(), 'r')))->getURI());
  }

  #[Test]
  public function stderr() {
    $this->assertEquals('php://stderr', (new File('php://stderr'))->getURI());
  }

  #[Test]
  public function stdout() {
    $this->assertEquals('php://stdout', (new File('php://stdout'))->getURI());
  }

  #[Test]
  public function stdin() {
    $this->assertEquals('php://stdin', (new File('php://stdin'))->getURI());
  }

  #[Test]
  public function xarSchemeAllowed() {
    $this->assertEquals('xar://test.xar?test.txt', (new File('xar://test.xar?test.txt'))->getURI());
  }

  #[Test]
  public function resSchemeAllowed() {
    $this->assertEquals('res://test.txt', (new File('res://test.txt'))->getURI());
  }

  #[Test]
  public function xarSchemeFileNameInRoot() {
    $this->assertEquals('test.txt', (new File('xar://test.xar?test.txt'))->getFileName());
  }

  #[Test]
  public function xarSchemePathInRoot() {
    $this->assertEquals('xar://test.xar?', (new File('xar://test.xar?test.txt'))->getPath());
  }

  #[Test]
  public function xarSchemeFileNameInSubdir() {
    $this->assertEquals('test.txt', (new File('xar://test.xar?dir/test.txt'))->getFileName());
  }

  #[Test]
  public function xarSchemePathInSubdir() {
    $this->assertEquals('xar://test.xar?dir', (new File('xar://test.xar?dir/test.txt'))->getPath());
  }

  #[Test]
  public function resSchemeFileName() {
    $this->assertEquals('test.txt', (new File('res://test.txt'))->getFileName());
  }

  #[Test]
  public function resSchemePath() {
    $this->assertEquals('res://', (new File('res://test.txt'))->getPath());
  }

  #[Test]
  public function resSchemePathInSubDir() {
    $this->assertEquals('res://dir', (new File('res://dir/test.txt'))->getPath());
  }

  #[Test]
  public function resSchemeExtension() {
    $this->assertEquals('txt', (new File('res://test.txt'))->getExtension());
  }

  #[Test]
  public function readable_stream() {
    $in= new MemoryInputStream('Test');
    $f= new File(Streams::readableUri($in));
    $f->open(File::READ);
    $bytes= $f->read($in->size());
    $f->close();

    $this->assertEquals('Test', $bytes);
  }

  #[Test]
  public function writeable_stream() {
    $out= new MemoryOutputStream();
    $f= new File(Streams::writeableUri($out));
    $f->open(File::READ);
    $f->write('Test');
    $f->close();

    $this->assertEquals('Test', $out->bytes());
  }

  #[Test]
  public function pathClassCanBeUsedAsBase() {
    $fn= $this->fileKnownToExist();
    $f= new File(new Path(dirname($fn)), basename($fn));
    $this->assertEquals($fn, $f->getURI());
  }

  #[Test]
  public function pathClassCanBeUsedAsArg() {
    $fn= $this->fileKnownToExist();
    $f= new File(new Path($fn));
    $this->assertEquals($fn, $f->getURI());
  }

  #[Test]
  public function filesize() {
    $fn= $this->fileKnownToExist();
    $f= new File($fn);
    $this->assertEquals(filesize($fn), $f->size());
  }

  #[Test]
  public function filesize_of_fd() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    $this->assertEquals(4, $f->size());
  }
}