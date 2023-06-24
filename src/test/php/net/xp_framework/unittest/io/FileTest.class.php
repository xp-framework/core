<?php namespace net\xp_framework\unittest\io;

use io\streams\{MemoryInputStream, MemoryOutputStream, Streams};
use io\{File, Folder, Path};
use lang\{IllegalArgumentException, Runtime};
use unittest\{Assert, Expect, Test};

class FileTest {

  /**
   * Return a file that is known to exist
   *
   * @return string
   */
  protected function fileKnownToExist() {
    return realpath(Runtime::getInstance()->getExecutable()->getFilename());
  }

  #[Test]
  public function sameInstanceIsEqual() {
    $f= new File($this->fileKnownToExist());
    Assert::equals($f, $f);
  }

  #[Test]
  public function sameFileIsEqual() {
    $fn= $this->fileKnownToExist();
    Assert::equals(new File($fn), new File($fn));
  }

  #[Test]
  public function differentFilesAreNotEqual() {
    Assert::notEquals(new File($this->fileKnownToExist()), new File(__FILE__));
  }

  #[Test]
  public function hashCodesNotEqualForTwoFileHandles() {
    $fn= $this->fileKnownToExist();
    $a= new File(fopen($fn, 'r'));
    $b= new File(fopen($fn, 'r'));
    Assert::notEquals($a->hashCode(), $b->hashCode());
  }

  #[Test]
  public function hashCodesEqualForSameFileHandles() {
    $handle= fopen($this->fileKnownToExist(), 'r');
    $a= new File($handle);
    $b= new File($handle);
    Assert::equals($a->hashCode(), $b->hashCode());
  }

  #[Test]
  public function hashCodesEqualForSameFiles() {
    $fn= $this->fileKnownToExist();
    Assert::equals(
      (new File($fn))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[Test]
  public function hashCodesNotEqualForHandleAndUri() {
    $fn= $this->fileKnownToExist();
    Assert::notEquals(
      (new File(fopen($fn, 'r')))->hashCode(),
      (new File($fn))->hashCode()
    );
  }

  #[Test]
  public function getURI() {
    $fn= $this->fileKnownToExist();
    Assert::equals($fn, (new File($fn))->getURI());
  }

  #[Test]
  public function getPath() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    Assert::equals($info['dirname'], (new File($fn))->getPath());
  }

  #[Test]
  public function getFileName() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    Assert::equals($info['basename'], (new File($fn))->getFileName());
  }

  #[Test]
  public function getExtension() {
    $fn= $this->fileKnownToExist();
    $info= pathinfo($fn);
    Assert::equals($info['extension'] ?? null, (new File($fn))->getExtension());
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
    Assert::equals($fn, (new File($fn))->getURI());
  }

  #[Test]
  public function composingFromFolderAndString() {
    $fn= $this->fileKnownToExist();
    Assert::equals($fn, (new File(new Folder(dirname($fn)), basename($fn)))->getURI());
  }

  #[Test]
  public function composingFromStringAndString() {
    $fn= $this->fileKnownToExist();
    Assert::equals($fn, (new File(dirname($fn), basename($fn)))->getURI());
  }

  #[Test]
  public function fromResource() {
    Assert::null((new File(fopen($this->fileKnownToExist(), 'r')))->getURI());
  }

  #[Test]
  public function stderr() {
    Assert::equals('php://stderr', (new File('php://stderr'))->getURI());
  }

  #[Test]
  public function stdout() {
    Assert::equals('php://stdout', (new File('php://stdout'))->getURI());
  }

  #[Test]
  public function stdin() {
    Assert::equals('php://stdin', (new File('php://stdin'))->getURI());
  }

  #[Test]
  public function xarSchemeAllowed() {
    Assert::equals('xar://test.xar?test.txt', (new File('xar://test.xar?test.txt'))->getURI());
  }

  #[Test]
  public function resSchemeAllowed() {
    Assert::equals('res://test.txt', (new File('res://test.txt'))->getURI());
  }

  #[Test]
  public function xarSchemeFileNameInRoot() {
    Assert::equals('test.txt', (new File('xar://test.xar?test.txt'))->getFileName());
  }

  #[Test]
  public function xarSchemePathInRoot() {
    Assert::equals('xar://test.xar?', (new File('xar://test.xar?test.txt'))->getPath());
  }

  #[Test]
  public function xarSchemeFileNameInSubdir() {
    Assert::equals('test.txt', (new File('xar://test.xar?dir/test.txt'))->getFileName());
  }

  #[Test]
  public function xarSchemePathInSubdir() {
    Assert::equals('xar://test.xar?dir', (new File('xar://test.xar?dir/test.txt'))->getPath());
  }

  #[Test]
  public function resSchemeFileName() {
    Assert::equals('test.txt', (new File('res://test.txt'))->getFileName());
  }

  #[Test]
  public function resSchemePath() {
    Assert::equals('res://', (new File('res://test.txt'))->getPath());
  }

  #[Test]
  public function resSchemePathInSubDir() {
    Assert::equals('res://dir', (new File('res://dir/test.txt'))->getPath());
  }

  #[Test]
  public function resSchemeExtension() {
    Assert::equals('txt', (new File('res://test.txt'))->getExtension());
  }

  #[Test]
  public function readable_stream() {
    $in= new MemoryInputStream('Test');
    $f= new File(Streams::readableUri($in));
    $f->open(File::READ);
    $bytes= $f->read($in->size());
    $f->close();

    Assert::equals('Test', $bytes);
  }

  #[Test]
  public function writeable_stream() {
    $out= new MemoryOutputStream();
    $f= new File(Streams::writeableUri($out));
    $f->open(File::READ);
    $f->write('Test');
    $f->close();

    Assert::equals('Test', $out->bytes());
  }

  #[Test]
  public function pathClassCanBeUsedAsBase() {
    $fn= $this->fileKnownToExist();
    $f= new File(new Path(dirname($fn)), basename($fn));
    Assert::equals($fn, $f->getURI());
  }

  #[Test]
  public function pathClassCanBeUsedAsArg() {
    $fn= $this->fileKnownToExist();
    $f= new File(new Path($fn));
    Assert::equals($fn, $f->getURI());
  }

  #[Test]
  public function filesize() {
    $fn= $this->fileKnownToExist();
    $f= new File($fn);
    Assert::equals(filesize($fn), $f->size());
  }

  #[Test]
  public function filesize_of_fd() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    Assert::equals(4, $f->size());
  }
}