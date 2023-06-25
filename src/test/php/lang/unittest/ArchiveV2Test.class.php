<?php namespace lang\unittest;

use lang\archive\Archive;
use unittest\{Assert, Test};

class ArchiveV2Test extends ArchiveTest {

  /** @return int */
  protected function version() { return 2; }

  #[Test]
  public function read_empty_archive_with_version_1() {
    $a= new Archive($this->file(1));
    $a->open(Archive::READ);
    Assert::equals(1, $a->version);
    $this->assertEntries($a, []);
  }

  #[Test]
  public function archive_with_version_1() {
    $a= new Archive($this->resource('v1.xar'));
    $a->open(Archive::READ);
    Assert::equals(1, $a->version);
  }

  #[Test]
  public function archive_version_1_contains_contained_text_file() {
    $a= new Archive($this->resource('v1.xar'));
    $a->open(Archive::READ);
    Assert::true($a->contains('contained.txt'));
  }

  #[Test]
  public function archive_version_1_contents() {
    $a= new Archive($this->resource('v1.xar'));
    $a->open(Archive::READ);
    $this->assertEntries($a, ['contained.txt' => "This file is contained in an archive!\n"]);
  }
}