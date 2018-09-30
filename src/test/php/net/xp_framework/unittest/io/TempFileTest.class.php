<?php namespace net\xp_framework\unittest\io;

use io\{TempFile, FileUtil, IOException};
use lang\{Environment, IllegalStateException};
use unittest\TestCase;

class TempFileTest extends TestCase {

  #[@test]
  public function can_create() {
    new TempFile();
  }

  #[@test]
  public function uses_tempdir() {
    $t= new TempFile();
    $this->assertEquals(realpath(Environment::tempDir()), realpath($t->getPath()));
  }

  #[@test]
  public function filename_begins_with_prefix() {
    $t= new TempFile('pre');
    $this->assertEquals('pre', substr($t->getFileName(), 0, 3));
  }

  #[@test]
  public function default_prefix() {
    $t= new TempFile();
    $this->assertEquals('tmp', substr($t->getFileName(), 0, 3));
  }

  #[@test]
  public function containing() {
    $t= (new TempFile())->containing('Test');
    try {
      $this->assertEquals('Test', FileUtil::getContents($t));
    } finally {
      $t->unlink();
    }
  }

  #[@test]
  public function containing_raises_error_when_file_cannot_be_written() {
    $t= new TempFile();
    $t->touch();
    $t->setPermissions(0000);

    try {
      $t->containing('Test');
      $this->fail('No exception raised', null, IOException::class);
    } catch (IOException $expected) {
      // OK
    } finally {
      $t->setPermissions(0600);
      $t->unlink();
    }
  }

  #[@test]
  public function containing_raises_error_when_file_is_open() {
    $t= new TempFile();
    $t->open(TempFile::WRITE);

    try {
      $t->containing('Test');
      $this->fail('No exception raised', null, IllegalStateException::class);
    } catch (IllegalStateException $expected) {
      // OK
    } finally {
      $t->close();
      $t->unlink();
    }
  }
}