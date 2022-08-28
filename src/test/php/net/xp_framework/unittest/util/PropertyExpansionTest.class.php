<?php namespace net\xp_framework\unittest\util;

use ArrayAccess, ReturnTypeWillChange;
use lang\ElementNotFoundException;
use unittest\actions\VerifyThat;
use unittest\{Expect, Test, TestCase, Values};
use util\Properties;

class PropertyExpansionTest extends TestCase {

  /**
   * Returns a fixture which expands `lookup.*` with a given expansion
   *
   * @param  string[] $lines
   * @param  [:var]|function(string): string $expansion
   * @return util.Properties
   */
  private function newFixture($lines, $expansion= ['TEST' => 'test']) {
    return (new Properties())
      ->expanding('lookup', $expansion)
      ->load(implode("\n", $lines))
    ;
  }

  #[Test]
  public function closure_lookup() {
    $prop= $this->newFixture(['test=${lookup.TEST}'], function($name) { return strtolower($name); });
    $this->assertEquals('test', $prop->readString(null, 'test'));
  }

  #[Test, Action(eval: 'new VerifyThat(fn() => !extension_loaded("xdebug"))')]
  public function callable_lookup() {
    $prop= $this->newFixture(['test=${lookup.TEST}'], 'strtolower');
    $this->assertEquals('test', $prop->readString(null, 'test'));
  }

  #[Test]
  public function map_lookup() {
    $prop= $this->newFixture(['test=${lookup.TEST}'], ['TEST' => 'test']);
    $this->assertEquals('test', $prop->readString(null, 'test'));
  }

  #[Test]
  public function arrayaccess_lookup() {
    $prop= $this->newFixture(['test=${lookup.TEST}'], new class implements ArrayAccess {

      #[ReturnTypeWillChange]
      function offsetExists($key) { return true; }

      #[ReturnTypeWillChange]
      function offsetGet($key) { return 'test'; }

      #[ReturnTypeWillChange]
      function offsetSet($key, $value) { /* Not implemented */ }

      #[ReturnTypeWillChange]
      function offsetUnset($key) { /* Not implemented */ }
    });
    $this->assertEquals('test', $prop->readString(null, 'test'));
  }

  #[Test]
  public function null_lookup_ignores_missing_expansion() {
    $prop= $this->newFixture(['test=${lookup.TEST}'], null);
    $this->assertEquals('', $prop->readString(null, 'test'));
  }

  #[Test, Expect(ElementNotFoundException::class), Values([null, false])]
  public function non_existant_lookup($return) {
    $prop= $this->newFixture(['test=${lookup.TEST}'], function($name) use($return) { return $return; });
    $prop->readString(null, 'test');
  }

  #[Test]
  public function lookup_inside_read_array() {
    $prop= $this->newFixture(['test[]=${lookup.TEST}']);
    $this->assertEquals(['test'], $prop->readArray(null, 'test'));
  }

  #[Test]
  public function lookup_inside_read_map() {
    $prop= $this->newFixture(['test[key]=${lookup.TEST}']);
    $this->assertEquals(['key' => 'test'], $prop->readMap(null, 'test'));
  }

  #[Test]
  public function lookup_inside_read_section() {
    $prop= $this->newFixture(['array[]=${lookup.TEST}', 'map[key]=${lookup.TEST}', 'scalar=${lookup.TEST}']);
    $this->assertEquals(
      ['array' => ['test'], 'map' => ['key' => 'test'], 'scalar' => 'test'],
      $prop->readSection(null)
    );
  }
}