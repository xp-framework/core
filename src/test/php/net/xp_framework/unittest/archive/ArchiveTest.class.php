<?php namespace net\xp_framework\unittest\archive;

use io\File;
use io\streams\{MemoryInputStream, MemoryOutputStream, Streams};
use lang\archive\Archive;
use lang\{ElementNotFoundException, FormatException};
use unittest\{Expect, Test};

/**
 * Base class for archive file tests
 *
 * @see  xp://net.xp_framework.unittest.archive.ArchiveV1Test
 * @see  xp://net.xp_framework.unittest.archive.ArchiveV2Test
 * @see   xp://lang.archive.Archive
 */
abstract class ArchiveTest extends \unittest\TestCase {
  
  /**
   * Returns the xar version to test
   *
   * @return  int
   */
  protected abstract function version();

  /** Helper to load a xar file from the class loading mechanism */
  protected function resource(string $name): File {
    return typeof($this)->getPackage()->getResourceAsStream($name);
  }

  /**
   * Asserts on entries in an archive
   *
   * @param   lang.archive.Archive a
   * @param   [:string] entries
   * @throws  unittest.AssertionFailedError
   */
  protected function assertEntries(Archive $a, array $entries) {
    $a->open(Archive::READ);
    $actual= [];
    while ($key= $a->getEntry()) {
      $actual[$key]= $a->extract($key);
    }
    $a->close();
    $this->assertEquals($entries, $actual);
  }
  
  /**
   * Returns an empty XAR archive as a file
   *
   * @return net.xp_framework.unittest.io.Buffer
   */
  protected function file($version) {
    static $header= [
      0 => "not.an.archive",
      1 => "CCA\1\0\0\0\0",
      2 => "CCA\2\0\0\0\0",
    ];

    return new File(Streams::readableFd(new MemoryInputStream($header[$version].str_repeat("\0", 248))));
  }

  #[Test, Expect(FormatException::class)]
  public function open_non_archive() {
    $a= new Archive($this->file(0));
    $a->open(Archive::READ);
  }

  #[Test]
  public function version_equals_stream_version() {
    $a= new Archive($this->file($this->version()));
    $a->open(Archive::READ);
    $this->assertEquals($this->version(), $a->version);
  }

  #[Test]
  public function version_equals_resource_version() {
    $a= new Archive($this->resource('v'.$this->version().'.xar'));
    $a->open(Archive::READ);
    $this->assertEquals($this->version(), $a->version);
  }

  #[Test]
  public function contains_non_existant() {
    $a= new Archive($this->file($this->version()));
    $a->open(Archive::READ);
    $this->assertFalse($a->contains('DOES-NOT-EXIST'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function extract_non_existant() {
    $a= new Archive($this->file($this->version()));
    $a->open(Archive::READ);
    $a->extract('DOES-NOT-EXIST');
  }

  #[Test]
  public function entries_for_empty_archive_are_an_empty_array() {
    $a= new Archive($this->file($this->version()));
    $a->open(Archive::READ);
    $this->assertEntries($a, []);
  }

  #[Test]
  public function contains_existant() {
    $a= new Archive($this->resource('v'.$this->version().'.xar'));
    $a->open(Archive::READ);
    $this->assertTrue($a->contains('contained.txt'));
  }

  #[Test]
  public function entries_contain_file() {
    $a= new Archive($this->resource('v'.$this->version().'.xar'));
    $a->open(Archive::READ);
    $this->assertEntries($a, ['contained.txt' => "This file is contained in an archive!\n"]);
  }

  #[Test]
  public function creating_empty_archive() {
    $out= new MemoryOutputStream();
    $a= new Archive(new File(Streams::writeableFd($out)));
    $a->open(Archive::CREATE);
    $a->create();
    
    $file= new File(Streams::readableFd(new MemoryInputStream($out->bytes())));
    $this->assertEntries(new Archive($file), []);
  }

  #[Test]
  public function creating_archive() {
    $contents= [
      'lang/Type.class.php'      => '<?php class Type { }',
      'lang/XPClass.class.php'   => '<?php class XPClass extends Type { }'
    ];

    $out= new MemoryOutputStream();
    $a= new Archive(new File(Streams::writeableFd($out)));
    $a->open(Archive::CREATE);
    foreach ($contents as $filename => $bytes) {
      $a->addBytes($filename, $bytes);
    };
    $a->create();

    $file= new File(Streams::readableFd(new MemoryInputStream($out->bytes())));
    $this->assertEntries(new Archive($file), $contents);
  }
}