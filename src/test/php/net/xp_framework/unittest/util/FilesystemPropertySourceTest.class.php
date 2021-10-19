<?php namespace net\xp_framework\unittest\util;

use io\{File, Files};
use lang\{Environment, IllegalArgumentException};
use unittest\{Expect, Test, TestCase};
use util\{FilesystemPropertySource, Properties};

/**
 * Testcase for util.Properties class.
 *
 * @deprecated
 * @see   xp://util.FilesystemPropertySource
 */
class FilesystemPropertySourceTest extends TestCase {
  protected $tempFile, $fixture;

  /** @return void */
  public function setUp() {
    $tempDir= realpath(Environment::tempDir());
    $this->fixture= new FilesystemPropertySource($tempDir);

    // Create a temporary ini file
    $this->tempFile= new File($tempDir, 'temp.ini');
    Files::write($this->tempFile, "[section]\nkey=value\n");
  }

  /** @return void */
  public function tearDown() {
    $this->tempFile->unlink();
  }

  #[Test]
  public function provides_existing_ini_file() {
    $this->assertTrue($this->fixture->provides('temp'));
  }

  #[Test]
  public function does_not_provide_non_existant_ini_file() {
    $this->assertFalse($this->fixture->provides('@@non-existant@@'));
  }

  #[Test]
  public function fetch_existing_ini_file() {
    $this->assertEquals(
      new Properties($this->tempFile->getURI()),
      $this->fixture->fetch('temp')
    );
  }

  #[Test, Expect(class: IllegalArgumentException::class, withMessage: '/No properties @@non-existant@@ found at .+/')]
  public function fetch_non_existant_ini_file() {
    $this->fixture->fetch('@@non-existant@@');
  }
}