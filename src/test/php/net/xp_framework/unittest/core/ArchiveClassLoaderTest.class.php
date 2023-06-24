<?php namespace net\xp_framework\unittest\core;

use io\Files;
use lang\archive\{Archive, ArchiveClassLoader};
use lang\{ClassNotFoundException, ElementNotFoundException, XPClass};
use unittest\Assert;
use unittest\{Expect, Test};

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
class ArchiveClassLoaderTest {
  private $fixture;
  
  /**
   * Sets fixture to point to archive.xar from src/test/resources/
   *
   * @return void
   */
  #[Before]
  public function setUp() {
    $this->fixture= new ArchiveClassLoader(new Archive(
      typeof($this)->getPackage()->getResourceAsStream('archive.xar')
    ));
  }

  #[Test]
  public function provides_class_in_archive() {
    Assert::true($this->fixture->providesClass('test.ClassLoadedFromArchive'));
  }

  #[Test]
  public function does_not_provide_non_existant_class() {
    Assert::false($this->fixture->providesClass('non.existant.Class'));
  }

  #[Test]
  public function provides_package_in_archive() {
    Assert::true($this->fixture->providesPackage('test'));
  }

  #[Test]
  public function does_not_provide_non_existant_package() {
    Assert::false($this->fixture->providesPackage('non.existant'));
  }

  #[Test]
  public function provides_resource_in_archive() {
    Assert::true($this->fixture->providesResource('test/package-info.xp'));
  }

  #[Test]
  public function does_not_provide_non_existant_resource() {
    Assert::false($this->fixture->providesResource('non/existant/resource.file'));
  }

  #[Test]
  public function load_existing_class_from_archive() {
    Assert::instance(XPClass::class, $this->fixture->loadClass('test.ClassLoadedFromArchive'));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loading_non_existant_class_raises_exception() {
    $this->fixture->loadClass('non.existant.Class');
  }

  #[Test]
  public function load_existing_resource_from_archive() {
    $contents= $this->fixture->getResource('test/package-info.xp');
    Assert::equals('<?php', substr($contents, 0, strpos($contents, "\n")));
  }

  #[Test]
  public function load_existing_resource_stream_from_archive() {
    $contents= Files::read($this->fixture->getResourceAsStream('test/package-info.xp'));
    Assert::equals('<?php', substr($contents, 0, strpos($contents, "\n")));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function load_non_existant_resource_from_archive() {
    $this->fixture->getResource('non/existant/resource.file');
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function load_non_existant_resource_stream_from_archive() {
    $this->fixture->getResourceAsStream('non/existant/resource.file');
  }

  #[Test]
  public function test_package_contents() {
    Assert::equals(
      ['ClassLoadedFromArchive.class.php', 'package-info.xp'],
      $this->fixture->packageContents('test')
    );
  }

  #[Test]
  public function non_existant_package_contents() {
    Assert::equals([], $this->fixture->packageContents('non.existant'));
  }

  #[Test]
  public function root_package_contents() {
    Assert::equals(['test/'], $this->fixture->packageContents(null));
  }
}