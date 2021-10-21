<?php namespace net\xp_framework\unittest\util;

use io\{File, Files};
use lang\{Environment, IllegalArgumentException, FileSystemClassLoader};
use unittest\{Expect, Test, TestCase};
use util\{ClassPathPropertySource, Properties};

class ClassPathPropertySourceTest extends TestCase {
  protected $tempFile, $fixture;

  /** @return void */
  public function setUp() {
    $tempDir= realpath(Environment::tempDir());
    $this->fixture= new ClassPathPropertySource(null, new FileSystemClassLoader($tempDir));

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
      ['key' => 'value'],
      $this->fixture->fetch('temp')->readSection('section')
    );
  }

  #[Test, Expect(class: IllegalArgumentException::class, withMessage: '/No properties @@non-existant@@ found at .+/')]
  public function fetch_non_existant_ini_file() {
    $this->fixture->fetch('@@non-existant@@');
  }
}