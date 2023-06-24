<?php namespace net\xp_framework\unittest\util;

use io\{IOException, TempFile};
use unittest\Assert;
use unittest\{Expect, Test};
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
    return (new Properties())->load((new TempFile())->containing($source));
  }

  #[Test]
  public function from_resource() {
    $prop= new Properties(typeof($this)->getPackage()->getResourceAsStream('example.ini')->getURI());
    Assert::equals('value', $prop->readString('section', 'key'));
  }

  #[Test, Expect(IOException::class)]
  public function throws_error_when_reading() {
    $p= new Properties('@@does-not-exist.ini@@');
    $p->readString('section', 'key');
  }

  #[Test]
  public function properties_from_same_file_are_equal() {
    Assert::equals(new Properties('a.ini'), new Properties('a.ini'));
  }

  #[Test]
  public function properties_from_different_file_are_not_equal() {
    Assert::notEquals(new Properties('a.ini'), new Properties('b.ini'));
  }
}