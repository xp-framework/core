<?php namespace net\xp_framework\unittest\io;

use io\{Files, IOException, TempFile};
use lang\{Environment, IllegalStateException};
use unittest\{Test, TestCase};

class TempFileTest extends TestCase {

  #[Test]
  public function can_create() {
    new TempFile();
  }

  #[Test]
  public function uses_tempdir() {
    $t= new TempFile();
    $this->assertEquals(realpath(Environment::tempDir()), realpath($t->getPath()));
  }

  #[Test]
  public function filename_begins_with_prefix() {
    $t= new TempFile('pre');
    $this->assertEquals('pre', substr($t->getFileName(), 0, 3));
  }

  #[Test]
  public function default_prefix() {
    $t= new TempFile();
    $this->assertEquals('tmp', substr($t->getFileName(), 0, 3));
  }

  #[Test]
  public function containing() {
    $t= (new TempFile())->containing('Test');
    try {
      $this->assertEquals('Test', Files::read($t));
    } finally {
      $t->unlink();
    }
  }

  #[Test]
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

  #[Test]
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

  #[Test]
  public function deleted_on_garbage_collection() {
    $t= new TempFile();
    $uri= $t->getURI();

    $t= null; // Force garbage collection
    $this->assertFalse(file_exists($uri));
  }

  #[Test]
  public function kept_after_garbage_collection_if_made_persistent() {
    $t= (new TempFile())->persistent();
    $uri= $t->getURI();

    $t= null; // Force garbage collection
    $this->assertTrue(file_exists($uri));
  }
}