<?php namespace util\unittest;

use io\{File, Files};
use lang\{Environment, IllegalArgumentException};
use test\{Assert, Before, Expect, Test};
use util\{FilesystemPropertySource, Properties};

class FilesystemPropertySourceTest {
  protected $tempFile, $fixture;

  #[Before]
  public function setUp() {
    $tempDir= realpath(Environment::tempDir());
    $this->fixture= new FilesystemPropertySource($tempDir);

    // Create a temporary ini file
    $this->tempFile= new File($tempDir, 'temp.ini');
    Files::write($this->tempFile, "[section]\nkey=value\n");
  }

  /** @return void */
  #[After]
  public function tearDown() {
    $this->tempFile->unlink();
  }

  #[Test]
  public function provides_existing_ini_file() {
    Assert::true($this->fixture->provides('temp'));
  }

  #[Test]
  public function does_not_provide_non_existant_ini_file() {
    Assert::false($this->fixture->provides('@@non-existant@@'));
  }

  #[Test]
  public function fetch_existing_ini_file() {
    Assert::equals(
      new Properties($this->tempFile->getURI()),
      $this->fixture->fetch('temp')
    );
  }

  #[Test, Expect(class: IllegalArgumentException::class, message: '/No properties @@non-existant@@ found at .+/')]
  public function fetch_non_existant_ini_file() {
    $this->fixture->fetch('@@non-existant@@');
  }
}