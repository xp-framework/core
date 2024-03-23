<?php namespace util\unittest;

use io\{IOException, TempFile};
use test\{Assert, Expect, Test};
use util\Properties;

class FileBasedPropertiesTest extends AbstractPropertiesTest {

  /**
   * Create a new properties object from a string source
   *
   * @param  string $source
   * @param  ?string $charset
   * @return util.Properties
   */
  protected function newPropertiesFrom(string $source, $charset= null): Properties {
    return (new Properties())->load((new TempFile())->containing($source), $charset);
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