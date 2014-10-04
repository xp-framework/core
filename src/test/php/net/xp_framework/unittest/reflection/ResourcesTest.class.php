<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\archive\Archive;
use io\File;


/**
 * TestCase for resource loading
 *
 * @see      xp://lang.ClassLoader
 * @purpose  Unittest
 */
class ResourcesTest extends TestCase {
  private $cl= null;

  /**
   * Sets up class loader
   */
  public function setUp() {
    $this->cl= \lang\ClassLoader::registerLoader(new \lang\archive\ArchiveClassLoader(new Archive($this
      ->getClass()
      ->getPackage()
      ->getPackage('lib')
      ->getResourceAsStream('three-and-four.xar'))
    ));
  }

  /**
   * Removes class loader
   */
  public function tearDown() {
    \lang\ClassLoader::removeLoader($this->cl);
  }

  /**
   * Helper method for getResource() and getResourceAsStream()
   *
   * @param   string contents
   */
  protected function assertManifestFile($contents) {
    $this->assertEquals(
      "[runnable]\nmain-class=\"remote.server.impl.ApplicationServer\"",
      trim($contents)
    );
  }
  
  #[@test]
  public function findResource() {
    $this->assertInstanceOf(
      'lang.archive.ArchiveClassLoader',
      \lang\ClassLoader::getDefault()->findResource('META-INF/manifest.ini')
    );
  }

  #[@test]
  public function getResource() {
    $this->assertManifestFile(\lang\ClassLoader::getDefault()->getResource('META-INF/manifest.ini'));
  }

  #[@test]
  public function getResourceAsStream() {
    $stream= \lang\ClassLoader::getDefault()->getResourceAsStream('META-INF/manifest.ini');
    $this->assertInstanceOf('io.File', $stream);
    $stream->open(FILE_MODE_READ);
    $this->assertManifestFile($stream->read($stream->size()));
    $stream->close();
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function nonExistantResource() {
    \lang\ClassLoader::getDefault()->getResource('::DOES-NOT-EXIST::');
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function nonExistantResourceStream() {
    \lang\ClassLoader::getDefault()->getResourceAsStream('::DOES-NOT-EXIST::');
  }
}
