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

  #[@test, @values([['Test', 'Test'], ['', null], ['1', 1]])]
  public function invoke_static_method($expect, $value) {
    $this->assertEquals($expect, GenericMethodFixture::{'newInstance<string>'}($value));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function invoke_static_method_failing() {
    GenericMethodFixture::{'newInstance<string>'}([1, 2, 3]);
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
  public function type_parameter_not_reported_in_getParameter() {
    $this->assertEquals('arg', $this->fixtureClass()->getMethod('get')->getParameter(0)->getName());
  }
}
