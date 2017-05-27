<?php namespace net\xp_framework\unittest\archive;

use lang\archive\Archive;

/**
 * Unittest v2 XARs
 *
 * @see  xp://net.xp_framework.unittest.archive.ArchiveTest
 */
class ArchiveV2Test extends ArchiveTest {

  /** @return int */
  protected function version() { return 2; }

  #[@test]
  public function read_empty_archive_with_version_1() {
    $a= new Archive($this->file(1));
    $a->open(Archive::READ);
    $this->assertEquals(1, $a->version);
    $this->assertEntries($a, []);
  }

  #[@test]
  public function archive_with_version_1() {
    $a= new Archive($this->resource('v1.xar'));
    $a->open(Archive::READ);
    $this->assertEquals(1, $a->version);
  }

  #[@test]
  public function archive_version_1_contains_contained_text_file() {
    $a= new Archive($this->resource('v1.xar'));
    $a->open(Archive::READ);
    $this->assertTrue($a->contains('contained.txt'));
  }

  #[@test]
  public function archive_version_1_contents() {
    $a= new Archive($this->resource('v1.xar'));
    $a->open(Archive::READ);
    $this->assertEntries($a, ['contained.txt' => "This file is contained in an archive!\n"]);
  }
}
