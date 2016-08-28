<?php namespace net\xp_framework\unittest\util;

use io\{File, FileUtil};
use lang\{System, IllegalArgumentException};
use util\{FilesystemPropertySource, Properties};

/**
 * Testcase for util.Properties class.
 *
 * @see   xp://util.FilesystemPropertySource
 */
class FilesystemPropertySourceTest extends \unittest\TestCase {
  protected $tempFile;
  protected $fixture;

  public function setUp() {
    $tempDir= realpath(System::tempDir());
    $this->fixture= new FilesystemPropertySource($tempDir);

    // Create a temporary ini file
    $this->tempFile= new File($tempDir, 'temp.ini');
    FileUtil::setContents($this->tempFile, "[section]\nkey=value\n");
  }

  public function tearDown() {
    $this->tempFile->unlink();
  }

  #[@test]
  public function provides_existing_ini_file() {
    $this->assertTrue($this->fixture->provides('temp'));
  }

  #[@test]
  public function does_not_provide_non_existant_ini_file() {
    $this->assertFalse($this->fixture->provides('@@non-existant@@'));
  }

  #[@test]
  public function fetch_existing_ini_file() {
    $this->assertEquals(
      new \util\Properties($this->tempFile->getURI()),
      $this->fixture->fetch('temp')
    );
  }

  #[@test, @expect(class= IllegalArgumentException::class, withMessage= '/No properties @@non-existant@@ found at .+/')]
  public function fetch_non_existant_ini_file() {
    $this->fixture->fetch('@@non-existant@@');
  }
}
