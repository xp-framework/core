<?php namespace net\xp_framework\unittest\core;

/**
 * TestCase for generic method invocation
 */
class GenericMethodInvocationTest extends \unittest\TestCase {

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
}
