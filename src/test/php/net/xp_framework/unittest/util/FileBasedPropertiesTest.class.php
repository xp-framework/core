<?php namespace net\xp_framework\unittest\util;

use io\{TempFile, IOException};
use util\Properties;

/**
 * Testcase for util.Properties class.
 *
 * @see   xp://net.xp_framework.unittest.util.AbstractPropertiesTest
 * @see   xp://util.Properties#fromFile
 * @test  xp://net.xp_framework.unittest.util.FilesystemPropertySourceTest
 */
class FileBasedPropertiesTest extends AbstractPropertiesTest {

  /** Create a new properties object from a string source */
  protected function newPropertiesFrom(string $source): Properties {
    $t= new TempFile();
    $t->out()->write($source);
    $t->close();
    return (new Properties())->load($t);
  }

  #[@test]
  public function from_resource() {
    $prop= new Properties($this->getClass()->getPackage()->getResourceAsStream('example.ini')->getURI());
    $this->assertEquals('value', $prop->readString('section', 'key'));
  }

  #[@test, @expect(IOException::class)]
  public function throws_error_when_reading() {
    $p= new Properties('@@does-not-exist.ini@@');
    $p->readString('section', 'key');
  }

  #[@test]
  public function properties_from_same_file_are_equal() {
    $this->assertEquals(new Properties('a.ini'), new Properties('a.ini'));
  }

  #[@test]
  public function properties_from_different_file_are_not_equal() {
    $this->assertNotEquals(new Properties('a.ini'), new Properties('b.ini'));
  }
}
