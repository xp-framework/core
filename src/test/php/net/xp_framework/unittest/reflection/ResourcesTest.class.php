<?php namespace net\xp_framework\unittest\reflection;

use lang\ClassLoader;
use lang\archive\Archive;
use lang\archive\ArchiveClassLoader;
use lang\ElementNotFoundException;
use io\File;

class ResourcesTest extends \unittest\TestCase {
  private $cl;

  /** @return void */
  public function setUp() {
    $this->cl= ClassLoader::registerLoader(new ArchiveClassLoader(new Archive(typeof($this)
      ->getPackage()
      ->getPackage('lib')
      ->getResourceAsStream('three-and-four.xar'))
    ));
  }

  /** @return void */
  public function tearDown() {
    ClassLoader::removeLoader($this->cl);
  }

  /**
   * Helper method for getResource() and getResourceAsStream()
   *
   * @param  string $contents
   * @throws unittest.AssertionFailedError
   */
  private function assertManifestFile($contents) {
    $this->assertEquals(
      "[runnable]\nmain-class=\"remote.server.impl.ApplicationServer\"",
      trim($contents)
    );
  }
  
  #[@test]
  public function findResource() {
    $this->assertInstanceOf(
      ArchiveClassLoader::class,
      ClassLoader::getDefault()->findResource('META-INF/manifest.ini')
    );
  }

  #[@test]
  public function getResource() {
    $this->assertManifestFile(ClassLoader::getDefault()->getResource('META-INF/manifest.ini'));
  }

  #[@test]
  public function getResourceAsStream() {
    $stream= ClassLoader::getDefault()->getResourceAsStream('META-INF/manifest.ini');
    $this->assertInstanceOf(File::class, $stream);
    $stream->open(File::READ);
    $this->assertManifestFile($stream->read($stream->size()));
    $stream->close();
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function nonExistantResource() {
    ClassLoader::getDefault()->getResource('::DOES-NOT-EXIST::');
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function nonExistantResourceStream() {
    ClassLoader::getDefault()->getResourceAsStream('::DOES-NOT-EXIST::');
  }
}
