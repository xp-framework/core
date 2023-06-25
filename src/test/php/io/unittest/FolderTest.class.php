<?php namespace io\unittest;

use io\{Folder, FolderEntries, IOException, Path};
use lang\Environment;
use unittest\{Assert, After, Expect, Test};

class FolderTest {
  private $folders= [];
  
  /**
   * Normalizes path by adding a trailing slash to the end if not already
   * existant.
   *
   * @param   string $path
   * @return  string
   */
  protected function normalize($path) {
    return rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  }

  /** @return string */
  private function tempFolder() {
    $temp= $this->normalize(realpath(Environment::tempDir())).md5(uniqid()).'.xp'.DIRECTORY_SEPARATOR;
    $this->folders[]= $temp;
    return $temp;
  }

  #[After]
  public function tearDown() {
    foreach ($this->folders as $folder) {
      is_dir($folder) && rmdir($folder);
    }
  }

  #[Test]
  public function sameInstanceIsEqual() {
    $f= new Folder($this->tempFolder());
    Assert::equals($f, $f);
  }

  #[Test]
  public function sameFolderIsEqual() {
    $temp= $this->tempFolder();
    Assert::equals(new Folder($temp), new Folder($temp));
  }

  #[Test]
  public function differentFoldersAreNotEqual() {
    Assert::notEquals(new Folder($this->tempFolder()), new Folder(__DIR__));
  }

  #[Test]
  public function exists() {
    Assert::false((new Folder($this->tempFolder()))->exists());
  }

  #[Test]
  public function create() {
    $f= new Folder($this->tempFolder());
    $f->create();
    Assert::true($f->exists());
  }

  #[Test]
  public function unlink() {
    $f= new Folder($this->tempFolder());
    $f->create();
    $f->unlink();
    Assert::false($f->exists());
  }

  #[Test]
  public function uriOfNonExistantFolder() {
    $temp= $this->tempFolder();
    Assert::equals($temp, (new Folder($temp))->getURI());
  }
  
  #[Test]
  public function uriOfExistantFolder() {
    $temp= $this->tempFolder();
    $f= new Folder($temp);
    $f->create();
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function uriOfDotFolder() {
    $temp= $this->tempFolder();
    $f= new Folder($temp, '.');
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function uriOfDotFolderTwoLevels() {
    $temp= $this->tempFolder();
    $f= new Folder($temp, '.', '.');
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function uriOfParentFolder() {
    $temp= $this->tempFolder();
    $f= new Folder($temp, '..');
    Assert::equals($this->normalize(dirname($temp)), $f->getURI());
  }

  #[Test]
  public function uriOfParentFolderOfSubFolder() {
    $temp= $this->tempFolder();
    $f= new Folder($temp, 'sub', '..');
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function uriOfParentFolderOfSubFolderTwoLevels() {
    $temp= $this->tempFolder();
    $f= new Folder($temp, 'sub1', 'sub2', '..', '..');
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function parentDirectoryOfRootIsRoot() {
    $f= new Folder(DIRECTORY_SEPARATOR, '..');
    Assert::equals($this->normalize(realpath(DIRECTORY_SEPARATOR)), $f->getURI());
  }

  #[Test]
  public function parentDirectoryOfRootIsRootTwoLevels() {
    $f= new Folder(DIRECTORY_SEPARATOR, '..', '..');
    Assert::equals($this->normalize(realpath(DIRECTORY_SEPARATOR)), $f->getURI());
  }

  #[Test]
  public function relativeDirectory() {
    $f= new Folder('tmp');
    Assert::equals($this->normalize($this->normalize(realpath('.')).'tmp'), $f->getURI());
  }

  #[Test]
  public function relativeDotDirectory() {
    $f= new Folder('./tmp');
    Assert::equals($this->normalize($this->normalize(realpath('.')).'tmp'), $f->getURI());
  }

  #[Test]
  public function relativeParentDirectory() {
    $f= new Folder('../tmp');
    Assert::equals($this->normalize($this->normalize(realpath('..')).'tmp'), $f->getURI());
  }

  #[Test]
  public function dotDirectory() {
    $f= new Folder('.');
    Assert::equals($this->normalize(realpath('.')), $f->getURI());
  }

  #[Test]
  public function parentDirectory() {
    $f= new Folder('..');
    Assert::equals($this->normalize(realpath('..')), $f->getURI());
  }

  #[Test]
  public function pathClassCanBeUsedAsBase() {
    $temp= $this->tempFolder();
    $f= new Folder(new Path($temp), '.');
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function pathClassCanBeUsedAsArg() {
    $temp= $this->tempFolder();
    $f= new Folder(new Path($temp));
    Assert::equals($temp, $f->getURI());
  }

  #[Test]
  public function entries() {
    Assert::instance(FolderEntries::class, (new Folder($this->tempFolder()))->entries());
  }

  #[Test, Expect(IOException::class)]
  public function entries_iteration_raises_exception_if_path_does_not_exist() {
    iterator_to_array((new Folder($this->tempFolder()))->entries());
  }
}