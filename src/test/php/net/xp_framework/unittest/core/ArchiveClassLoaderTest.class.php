<?php namespace net\xp_framework\unittest\core;

use lang\archive\{Archive, ArchiveClassLoader};
use lang\{ClassNotFoundException, ElementNotFoundException, XPClass};
use io\FileUtil;

/**
 * TestCase for archive class loading
 *
 * Relies on an archive.xar file existing in the resources directory
 * with the following contents:
 *
 * ```sh
 * $ xar tvf archive.xar
 *    92 test/ClassLoadedFromArchive.class.php
 *   104 test/package-info.xp
 * ```
 * 
 * @see   xp://lang.archive.ArchiveClassLoader
 */
class ArchiveClassLoaderTest extends \unittest\TestCase {
  private $fixture;
  
  /**
   * Sets fixture to point to archive.xar from src/test/resources/
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= new ArchiveClassLoader(new Archive(
      typeof($this)->getPackage()->getResourceAsStream('archive.xar')
    ));
  }

  #[@test]
  public function provides_class_in_archive() {
    $this->assertTrue($this->fixture->providesClass('test.ClassLoadedFromArchive'));
  }

  #[@test]
  public function does_not_provide_non_existant_class() {
    $this->assertFalse($this->fixture->providesClass('non.existant.Class'));
  }

  #[@test]
  public function provides_package_in_archive() {
    $this->assertTrue($this->fixture->providesPackage('test'));
  }

  #[@test]
  public function does_not_provide_non_existant_package() {
    $this->assertFalse($this->fixture->providesPackage('non.existant'));
  }

  #[@test]
  public function provides_resource_in_archive() {
    $this->assertTrue($this->fixture->providesResource('test/package-info.xp'));
  }

  #[@test]
  public function does_not_provide_non_existant_resource() {
    $this->assertFalse($this->fixture->providesResource('non/existant/resource.file'));
  }

  #[@test]
  public function load_existing_class_from_archive() {
    $this->assertInstanceOf(XPClass::class, $this->fixture->loadClass('test.ClassLoadedFromArchive'));
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function loading_non_existant_class_raises_exception() {
    $this->fixture->loadClass('non.existant.Class');
  }

  #[@test]
  public function load_existing_resource_from_archive() {
    $contents= $this->fixture->getResource('test/package-info.xp');
    $this->assertEquals('<?php', substr($contents, 0, strpos($contents, "\n")));
  }

  #[@test]
  public function load_existing_resource_stream_from_archive() {
    $contents= FileUtil::getContents($this->fixture->getResourceAsStream('test/package-info.xp'));
    $this->assertEquals('<?php', substr($contents, 0, strpos($contents, "\n")));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function load_non_existant_resource_from_archive() {
    $this->fixture->getResource('non/existant/resource.file');
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function load_non_existant_resource_stream_from_archive() {
    $this->fixture->getResourceAsStream('non/existant/resource.file');
  }

  #[@test]
  public function test_package_contents() {
    $this->assertEquals(
      ['ClassLoadedFromArchive.class.php', 'package-info.xp'],
      $this->fixture->packageContents('test')
    );
  }

  #[@test]
  public function non_existant_package_contents() {
    $this->assertEquals([], $this->fixture->packageContents('non.existant'));
  }

  #[@test]
  public function root_package_contents() {
    $this->assertEquals(['test/'], $this->fixture->packageContents(null));
  }
}
