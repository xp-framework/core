<?php namespace net\xp_framework\unittest\util;

use ReturnTypeWillChange;
use lang\ElementNotFoundException;
use unittest\actions\VerifyThat;
use unittest\{Expect, Test, TestCase, Values};
use util\Properties;

class PropertyExpansionTest extends TestCase {

  /**
   * Returns a fixture which expands `lookup.*` with a given expansion
   *
   * @param  [:var]|function(string): string $expansion
   * @return util.Properties
   */
  private function newFixture($expansion) {
    return (new Properties())
      ->expanding('lookup', $expansion)
      ->load("[section]\n".'test=${lookup.TEST}')
    ;
  }

  #[Test]
  public function closure_lookup() {
    $prop= $this->newFixture(function($name) { return strtolower($name); });
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[Test, Action(eval: 'new VerifyThat(fn() => !extension_loaded("xdebug"))')]
  public function callable_lookup() {
    $prop= $this->newFixture('strtolower');
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[Test]
  public function map_lookup() {
    $prop= $this->newFixture(['TEST' => 'test']);
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[Test]
  public function arrayaccess_lookup() {
    $prop= $this->newFixture(new class implements \ArrayAccess {

      #[ReturnTypeWillChange]
      function offsetExists($key) { return true; }

      #[ReturnTypeWillChange]
      function offsetGet($key) { return 'test'; }

      #[ReturnTypeWillChange]
      function offsetSet($key, $value) { /* Not implemented */ }

      #[ReturnTypeWillChange]
      function offsetUnset($key) { /* Not implemented */ }
    });
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[Test]
  public function null_lookup_ignores_missing_expansion() {
    $prop= $this->newFixture(null);
    $this->assertEquals('', $prop->readString('section', 'test'));
  }

  #[Test, Expect(ElementNotFoundException::class), Values([null, false])]
  public function non_existant_lookup($return) {
    $this->newFixture(function($name) use($return) { return $return; })->readString('section', 'test');
  }
}