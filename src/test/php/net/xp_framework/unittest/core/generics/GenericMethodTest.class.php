<?php namespace net\xp_framework\unittest\core\generics;

use lang\XPClass;
use lang\Primitive;

/**
 * TestCase for generic methods
 */
class GenericMethodTest extends \unittest\TestCase {

  /** @return lang.XPClass */
  protected function fixtureClass() {
    return XPClass::forName('net.xp_framework.unittest.core.generics.GenericMethodFixture');
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
  public function invoke_static_method_reflectively() {
    $method= $this->fixtureClass()->getMethod('asList')->newGenericMethod([Primitive::$STRING]);
    $this->assertEquals(
      ['Test'],
      $method->invoke(null, [['Test']])->elements()
    );
  }

  #[@test, @expect(
  #  class = 'lang.IllegalArgumentException',
  #  withMessage= 'Method asList expects 1 component(s) <T>, 0 argument(s) given'
  #)]
  public function newGenericMethod_with_missing_argument() {
    $this->fixtureClass()->getMethod('asList')->newGenericMethod([]);
  }

  #[@test, @expect(
  #  class = 'lang.IllegalArgumentException',
  #  withMessage= 'Method asList expects 1 component(s) <T>, 2 argument(s) given'
  #)]
  public function newGenericMethod_with_too_many_arguments() {
    $this->fixtureClass()->getMethod('asList')->newGenericMethod([Primitive::$STRING, Primitive::$STRING]);
  }

  #[@test]
  public function method_is_generic_definition() {
    $this->assertTrue($this->fixtureClass()->getMethod('get')->isGenericDefinition());
  }

  #[@test]
  public function this_method_is_not_generic_definition() {
    $this->assertFalse($this->getClass()->getMethod(__FUNCTION__)->isGenericDefinition());
  }

  #[@test]
  public function methods_generic_components() {
    $this->assertEquals(['T'], $this->fixtureClass()->getMethod('get')->genericComponents());
  }

  #[@test, @expect('lang.IllegalStateException')]
  public function this_method_has_no_generic_components() {
    $this->getClass()->getMethod(__FUNCTION__)->genericComponents();
  }

  #[@test]
  public function new_generic_method_is_generic() {
    $method= $this->fixtureClass()->getMethod('asList')->newGenericMethod([Primitive::$STRING]);
    $this->assertTrue($method->isGeneric());
  }

  #[@test]
  public function new_generic_methods_arguments() {
    $method= $this->fixtureClass()->getMethod('asList')->newGenericMethod([Primitive::$STRING]);
    $this->assertEquals([Primitive::$STRING], $method->genericArguments());
  }

  #[@test, @expect('lang.IllegalStateException')]
  public function generic_definition_methoddoes_not_have_arguments() {
    $this->fixtureClass()->getMethod('asList')->genericArguments();
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

  #[@test]
  public function generic_method_inside_generic_class() {
    $this->assertEquals(
      'public static self<L> of<L>(L[] $args)',
      XPClass::forName('net.xp_framework.unittest.core.generics.NSListOf')->getMethod('of')->toString()
    );
  }

  #[@test]
  public function generic_method_inside_generic_instance() {
    $instance= create('new net.xp_framework.unittest.core.generics.NSListOf<string>');
    $this->assertEquals(
      'public static self<L> of<L>(L[] $args)',
      $instance->getClass()->getMethod('of')->toString()
    );
  }
}
