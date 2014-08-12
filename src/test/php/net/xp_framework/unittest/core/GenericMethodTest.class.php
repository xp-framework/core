<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;

/**
 * TestCase for generic methods
 */
class GenericMethodTest extends \unittest\TestCase {

  /** @return lang.XPClass */
  protected function fixtureClass() {
    return XPClass::forName('net.xp_framework.unittest.core.GenericMethodFixture');
  }

  #[@test, @values([['Test', 'Test'], ['', null], ['1', 1]])]
  public function invoke_method($expect, $value) {
    $this->assertEquals($expect, (new GenericMethodFixture())->{'get<string>'}($value));
  }

  #[@test, @expect('lang.ClassCastException')]
  public function invoke_method_failing() {
    (new GenericMethodFixture())->{'get<string>'}([1, 2, 3]);
  }

  #[@test]
  public function invoke_static_method() {
    $this->assertEquals(['a', 'b', 'c'], GenericMethodFixture::{'asList<string>'}(['a', 'b', 'c'])->elements());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function invoke_static_method_failing() {
    GenericMethodFixture::{'asList<string>'}(['a', 'b', 3]);
  }

  #[@test]
  public function invoke_method_with_two_components() {
    $this->assertEquals(
      create('new util.collections.HashTable<string, unittest.TestCase>'),
      GenericMethodFixture::{'newHash<string, unittest.TestCase>'}()
    );
  }

  #[@test]
  public function method_is_generic() {
    $this->assertTrue($this->fixtureClass()->getMethod('get')->isGeneric());
  }

  #[@test]
  public function type_parameter_not_reported_in_numParameters() {
    $this->assertEquals(1, $this->fixtureClass()->getMethod('get')->numParameters());
  }

  #[@test]
  public function type_parameter_not_reported_in_getParameters() {
    $this->assertEquals(['arg'], array_map(
      function($e) { return $e->getName(); },
      $this->fixtureClass()->getMethod('get')->getParameters()
    ));
  }

  #[@test]
  public function type_parameter_not_reported_in_getParameters_for_newHash() {
    $this->assertEquals([], array_map(
      function($e) { return $e->getName(); },
      $this->fixtureClass()->getMethod('newHash')->getParameters()
    ));
  }

  #[@test]
  public function type_parameter_not_reported_in_getParameter_for_get() {
    $this->assertEquals('arg', $this->fixtureClass()->getMethod('get')->getParameter(0)->getName());
  }

  #[@test]
  public function get_method_to_string_contains_generic_marker() {
    $this->assertEquals(
      'public T get<T>([var $arg= null])',
      $this->fixtureClass()->getMethod('get')->toString()
    );
  }

  #[@test]
  public function asList_method_to_string_contains_generic_marker() {
    $this->assertEquals(
      'public static util.collections.IList<T> asList<T>(T[] $arg)',
      $this->fixtureClass()->getMethod('asList')->toString()
    );
  }

  #[@test]
  public function newHash_method_to_string_contains_generic_marker() {
    $this->assertEquals(
      'public static util.collections.HashTable<K, V> newHash<K, V>()',
      $this->fixtureClass()->getMethod('newHash')->toString()
    );
  }
}
