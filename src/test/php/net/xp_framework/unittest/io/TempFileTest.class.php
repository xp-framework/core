<?php namespace net\xp_framework\unittest\io;

use io\TempFile;
use lang\Environment;
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
}