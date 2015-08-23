<?php namespace net\xp_framework\unittest\util;

use io\File;
use io\streams\Streams;
use io\streams\MemoryInputStream;;
use io\IOException;
use util\Properties;

/**
 * Testcase for util.Properties class.
 *
 * @see   xp://net.xp_framework.unittest.util.AbstractPropertiesTest
 * @see   xp://util.Properties#fromFile
 * @test  xp://net.xp_framework.unittest.util.FilesystemPropertySourceTest
 */
class FileBasedPropertiesTest extends AbstractPropertiesTest {
  protected static $fileStreamAdapter;
  
  static function __static() {
    self::$fileStreamAdapter= \lang\ClassLoader::defineClass('FileStreamAdapter', 'io.File', [], '{
      protected $stream= null;
      public function __construct($stream) { $this->stream= $stream; }
      public function exists() { return null !== $this->stream; }
      public function getURI() { return \io\streams\Streams::readableUri($this->stream); }
      public function getInputStream() { return $this->stream; }
    }');
  }

  /**
   * Create a new properties object from a string source
   *
   * @param   string source
   * @return  util.Properties
   */
  protected function newPropertiesFrom($source) {
    return Properties::fromFile(self::$fileStreamAdapter->newInstance(new MemoryInputStream($source)));
  }

  #[@test, @expect(IOException::class)]
  public function fromNonExistantFile() {
    Properties::fromFile(new File('@@does-not-exist.ini@@'));
  }

  #[@test]
  public function fromFile() {
    $p= Properties::fromFile($this->getClass()->getPackage()->getResourceAsStream('example.ini'));
    $this->assertEquals('value', $p->readString('section', 'key'));
  }

  #[@test]
  public function lazyRead() {
    $p= new Properties('@@does-not-exist.ini@@');
    
    // This cannot be done via expect annotation because it would also catch if
    // an exception was thrown from util.Properties' constructor. We explicitely
    // want the exception to be thrown later on!
    try {
      $p->readString('section', 'key');
      $this->fail('Expected exception not thrown', null, 'io.IOException');
    } catch (IOException $expected) {
      \xp::gc();
    }
  }

  #[@test]
  public function propertiesFromSameFileAreEqual() {
    $one= Properties::fromFile($this->getClass()->getPackage()->getResourceAsStream('example.ini'));
    $two= Properties::fromFile($this->getClass()->getPackage()->getResourceAsStream('example.ini'));

    $this->assertFalse($one === $two);
    $this->assertTrue($one->equals($two));
  }

  #[@test]
  public function propertiesFromOtherFilesAreNotEqual() {
    $this->assertNotEquals(new Properties('a.ini'), new Properties('b.ini'));
  }
}
