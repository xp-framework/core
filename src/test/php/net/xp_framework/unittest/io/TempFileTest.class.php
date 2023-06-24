<?php namespace net\xp_framework\unittest\io;

use io\{Files, IOException, TempFile};
use lang\{Environment, IllegalStateException};
use unittest\{Assert, Test};

class TempFileTest {

  #[Test]
  public function can_create() {
    new TempFile();
  }

  #[Test]
  public function uses_tempdir() {
    $t= new TempFile();
    Assert::equals(realpath(Environment::tempDir()), realpath($t->getPath()));
  }

  #[Test]
  public function filename_begins_with_prefix() {
    $t= new TempFile('pre');
    Assert::equals('pre', substr($t->getFileName(), 0, 3));
  }

  #[Test]
  public function default_prefix() {
    $t= new TempFile();
    Assert::equals('tmp', substr($t->getFileName(), 0, 3));
  }

  #[Test]
  public function containing() {
    $t= (new TempFile())->containing('Test');
    try {
      Assert::equals('Test', Files::read($t));
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
    Assert::false(file_exists($uri));
  }

  #[Test]
  public function kept_after_garbage_collection_if_made_persistent() {
    $t= (new TempFile())->persistent();
    $uri= $t->getURI();

    $t= null; // Force garbage collection
    Assert::true(file_exists($uri));
  }
}