<?php namespace net\xp_framework\unittest\util;

use util\Properties;
use lang\ElementNotFoundException;

class PropertyExpansionTest extends \unittest\TestCase {

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

  #[@test]
  public function closure_lookup() {
    $prop= $this->newFixture(function($name) { return strtolower($name); });
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[@test]
  public function callable_lookup() {
    $prop= $this->newFixture('strtolower');
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[@test]
  public function map_lookup() {
    $prop= $this->newFixture(['TEST' => 'test']);
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[@test]
  public function arrayaccess_lookup() {
    $prop= $this->newFixture(newinstance(\ArrayAccess::class, [], [
      'offsetExists' => function($key) { return true; },
      'offsetGet'    => function($key) { return 'test'; },
      'offsetSet'    => function($key, $value) { /* Not implemented */ },
      'offsetUnset'  => function($key) { /* Not implemented */ }
    ]));
    $this->assertEquals('test', $prop->readString('section', 'test'));
  }

  #[@test, @expect(ElementNotFoundException::class), @values([null, false])]
  public function non_existant_lookup($return) {
    $this->newFixture(function($name) use($return) { return $return; })->readString('section', 'test');
  }
}