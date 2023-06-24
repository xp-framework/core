<?php namespace net\xp_framework\unittest\util;

use io\{File, Files};
use lang\{Environment, FileSystemClassLoader, IllegalArgumentException};
use unittest\{Assert, Expect, Test};
use util\{ClassPathPropertySource, Properties};

class ClassPathPropertySourceTest {
  protected $tempFile, $fixture;

  /** @return void */
  #[Before]
  public function setUp() {
    $tempDir= realpath(Environment::tempDir());
    $this->fixture= new ClassPathPropertySource(null, new FileSystemClassLoader($tempDir));

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
      ['key' => 'value'],
      $this->fixture->fetch('temp')->readSection('section')
    );
  }

  #[Test, Expect(class: IllegalArgumentException::class, withMessage: '/No properties @@non-existant@@ found at .+/')]
  public function fetch_non_existant_ini_file() {
    $this->fixture->fetch('@@non-existant@@');
  }
}