<?php namespace net\xp_framework\unittest\reflection;

use lang\ElementNotFoundException;
use lang\reflect\Method;
use unittest\Assert;
use unittest\{Expect, Ignore, Test, Values};

class MethodBasicsTest extends MethodsTest {

  #[Test, Ignore('TODO: Add parent')]
  public function methods_contains_equals_from_Object() {
    $fixture= $this->type();
    $equals= $fixture->getMethod('equals');
    foreach ($fixture->getMethods() as $method) {
      if ($equals->equals($method)) return;
    }
    $this->fail('Equals method not contained', null, $fixture->getMethods());
  }

  #[Test, Ignore('TODO: Add parent')]
  public function declared_methods_does_not_contain_hashCode_from_Object() {
    $fixture= $this->type();
    $equals= $fixture->getMethod('equals');
    foreach ($fixture->getDeclaredMethods() as $method) {
      if ($equals->equals($method)) $this->fail('Equals method contained', null, $fixture->getDeclaredMethods());
    }
  }
  
  #[Test]
  public function declaring_class() {
    $fixture= $this->type('{ public function declared() { }}');
    Assert::equals($fixture, $fixture->getMethod('declared')->getDeclaringClass());
  }

  #[Test, Ignore('TODO: Add parent')]
  public function declaring_class_of_inherited_method() {
    $fixture= $this->type();
    Assert::equals($fixture->getParentclass(), $fixture->getMethod('equals')->getDeclaringClass());
  }

  #[Test]
  public function has_method_for_existant() {
    Assert::true($this->type('{ public function declared() { }}')->hasMethod('declared'));
  }

  #[Test]
  public function has_method_for_non_existant() {
    Assert::false($this->type()->hasMethod('@@nonexistant@@'));
  }

  #[Test, Values(['__construct', '__destruct', '__static', '__import'])]
  public function has_method_for_special($named) {
    Assert::false($this->type()->hasMethod($named));
  }

  #[Test]
  public function get_existant_method() {
    Assert::instance(Method::class, $this->type('{ public function declared() { }}')->getMethod('declared'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function get_non_existant_method() {
    $this->type()->getMethod('@@nonexistant@@');
  }
  
  #[Test, Expect(ElementNotFoundException::class), Values(['__construct', '__destruct', '__static', '__import'])]
  public function get_method_for_special($named) {
    $this->type()->getMethod($named);
  }

  #[Test]
  public function name() {
    Assert::equals('fixture', $this->method('public function fixture() { }')->getName());
  }

  #[Test]
  public function no_parameters() {
    Assert::equals(0, $this->method('public function fixture() { }')->numParameters());
  }

  #[Test]
  public function one_parameter() {
    Assert::equals(1, $this->method('public function fixture($param) { }')->numParameters());
  }

  #[Test]
  public function two_parameters() {
    Assert::equals(2, $this->method('public function fixture($a, $b) { }')->numParameters());
  }

  #[Test]
  public function with_comment() {
    Assert::equals('Test', $this->method('/** Test */ public function fixture() { }')->getComment());
  }

  #[Test]
  public function without_comment() {
    Assert::equals('', $this->method('public function fixture() { }')->getComment());
  }

  #[Test]
  public function equality() {
    $fixture= $this->type('{ public function hashCode() { } }');
    Assert::equals($fixture->getMethod('hashCode'), $fixture->getMethod('hashCode'));
  }

  #[Test, Ignore('TODO: Add parent')]
  public function a_method_is_not_equal_to_parent_method() {
    $fixture= $this->type('{ public function hashCode() { } }');
    Assert::notEquals($fixture->getMethod('hashCode'), $fixture->getParentclass()->getMethod('hashCode'));
  }

  #[Test]
  public function a_method_is_not_equal_to_null() {
    Assert::notEquals($this->method('public function fixture() { }'), null);
  }

  #[Test, Values([['public function fixture() { }', 'public var fixture()'], ['private function fixture() { }', 'private var fixture()'], ['protected function fixture() { }', 'protected var fixture()'], ['static function fixture() { }', 'public static var fixture()'], ['private static function fixture() { }', 'private static var fixture()'], ['protected static function fixture() { }', 'protected static var fixture()'], ['public function fixture($param) { }', 'public var fixture(var $param)'], ['/** @return void */ public function fixture() { }', 'public void fixture()'], ['/** @param string[] */ public function fixture($param) { }', 'public var fixture(string[] $param)'], ['/** @throws lang.IllegalAccessException */ public function fixture() { }', 'public var fixture() throws lang.IllegalAccessException']])]
  public function string_representation($declaration, $expected) {
    Assert::equals($expected, $this->method($declaration)->toString());
  }

  #[Test]
  public function trait_comment() {
    Assert::equals('Compares a given value to this', $this->type()->getMethod('compareTo')->getComment());
  }

  #[Test]
  public function trait_return_type() {
    Assert::equals('int', $this->type()->getMethod('compareTo')->getReturnTypeName());
  }
}