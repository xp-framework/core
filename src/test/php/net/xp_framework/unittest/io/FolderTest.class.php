<?php namespace net\xp_framework\unittest\io;

use io\{Folder, FolderEntries, Path, IOException};
use lang\System;
use unittest\PrerequisitesNotMetError;

class FolderTest extends \unittest\TestCase {
  private $temp;
  
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

  /**
   * Sets up test case - initializes directory in %TEMP%
   *
   * @return void
   */
  public function setUp() {
    $this->temp= $this->normalize(realpath(System::tempDir())).md5(uniqid()).'.xp'.DIRECTORY_SEPARATOR;
    if (is_dir($this->temp) && !rmdir($this->temp)) {
      throw new PrerequisitesNotMetError('Fixture directory exists, but cannot remove', null, $this->temp);
    }
  }
  
  /**
   * Deletes directory in %TEMP% (including any files inside) if existant
   *
   * @return void
   */
  public function tearDown() {
    is_dir($this->temp) && rmdir($this->temp);
  }

  #[@test]
  public function sameInstanceIsEqual() {
    $f= new Folder($this->temp);
    $this->assertEquals($f, $f);
  }

  #[@test]
  public function sameFolderIsEqual() {
    $this->assertEquals(new Folder($this->temp), new Folder($this->temp));
  }

  #[@test]
  public function differentFoldersAreNotEqual() {
    $this->assertNotEquals(new Folder($this->temp), new Folder(__DIR__));
  }

  #[@test]
  public function exists() {
    $this->assertFalse((new Folder($this->temp))->exists());
  }

  #[@test]
  public function create() {
    $f= new Folder($this->temp);
    $f->create();
    $this->assertTrue($f->exists());
  }

  #[@test]
  public function unlink() {
    $f= new Folder($this->temp);
    $f->create();
    $f->unlink();
    $this->assertFalse($f->exists());
  }

  #[@test]
  public function uriOfNonExistantFolder() {
    $this->assertEquals($this->temp, (new Folder($this->temp))->getURI());
  }
  
  #[@test]
  public function uriOfExistantFolder() {
    $f= new Folder($this->temp);
    $f->create();
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function uriOfDotFolder() {
    $f= new Folder($this->temp, '.');
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function uriOfDotFolderTwoLevels() {
    $f= new Folder($this->temp, '.', '.');
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function uriOfParentFolder() {
    $f= new Folder($this->temp, '..');
    $this->assertEquals($this->normalize(dirname($this->temp)), $f->getURI());
  }

  #[@test]
  public function uriOfParentFolderOfSubFolder() {
    $f= new Folder($this->temp, 'sub', '..');
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function uriOfParentFolderOfSubFolderTwoLevels() {
    $f= new Folder($this->temp, 'sub1', 'sub2', '..', '..');
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function parentDirectoryOfRootIsRoot() {
    $f= new Folder(DIRECTORY_SEPARATOR, '..');
    $this->assertEquals($this->normalize(realpath(DIRECTORY_SEPARATOR)), $f->getURI());
  }

  #[@test]
  public function parentDirectoryOfRootIsRootTwoLevels() {
    $f= new Folder(DIRECTORY_SEPARATOR, '..', '..');
    $this->assertEquals($this->normalize(realpath(DIRECTORY_SEPARATOR)), $f->getURI());
  }

  #[@test]
  public function relativeDirectory() {
    $f= new Folder('tmp');
    $this->assertEquals($this->normalize($this->normalize(realpath('.')).'tmp'), $f->getURI());
  }

  #[@test]
  public function relativeDotDirectory() {
    $f= new Folder('./tmp');
    $this->assertEquals($this->normalize($this->normalize(realpath('.')).'tmp'), $f->getURI());
  }

  #[@test]
  public function relativeParentDirectory() {
    $f= new Folder('../tmp');
    $this->assertEquals($this->normalize($this->normalize(realpath('..')).'tmp'), $f->getURI());
  }

  #[@test]
  public function dotDirectory() {
    $f= new Folder('.');
    $this->assertEquals($this->normalize(realpath('.')), $f->getURI());
  }

  #[@test]
  public function parentDirectory() {
    $f= new Folder('..');
    $this->assertEquals($this->normalize(realpath('..')), $f->getURI());
  }

  #[@test]
  public function pathClassCanBeUsedAsBase() {
    $f= new Folder(new Path($this->temp), '.');
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function pathClassCanBeUsedAsArg() {
    $f= new Folder(new Path($this->temp));
    $this->assertEquals($this->temp, $f->getURI());
  }

  #[@test]
  public function entries() {
    $this->assertInstanceOf(FolderEntries::class, (new Folder($this->temp))->entries());
  }

  #[@test, @expect(IOException::class)]
  public function entries_iteration_raises_exception_if_path_does_not_exist() {
    iterator_to_array((new Folder($this->temp))->entries());
  }
}
