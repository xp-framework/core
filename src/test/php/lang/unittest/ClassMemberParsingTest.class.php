<?php namespace lang\unittest;

use lang\XPClass;
use lang\unittest\fixture\Namespaced;
use unittest\{Assert, Test, Values};

class ClassMemberParsingTest {
  const CONSTANT = 'local';
  protected static $value = 'static';

  #[Test, Values([self::CONSTANT])]
  public function class_constant_via_self($value) {
    Assert::equals('local', $value);
  }

  #[Test, Values(eval: '[new Name(self::CONSTANT)]')]
  public function class_constant_via_self_inside_new($value) {
    Assert::equals(new Name('local'), $value);
  }

  #[Test, Values([ClassMemberParsingTest::CONSTANT])]
  public function class_constant_via_unqualified_current($value) {
    Assert::equals('local', $value);
  }

  #[Test, Values([\lang\unittest\ClassMemberParsingTest::CONSTANT])]
  public function class_constant_via_fully_qualified_current($value) {
    Assert::equals('local', $value);
  }

  #[Test, Values([Namespaced::CONSTANT])]
  public function class_constant_via_imported_classname($value) {
    Assert::equals('namespaced', $value);
  }

  #[Test, Values([\lang\unittest\fixture\Namespaced::CONSTANT])]
  public function class_constant_via_fully_qualified($value) {
    Assert::equals('namespaced', $value);
  }

  #[Test, Values(eval: '[self::$value]')]
  public function static_member_via_self($value) {
    Assert::equals('static', $value);
  }

  #[Test, Values(eval: '[new Name(self::$value)]')]
  public function static_member_via_self_inside_new($value) {
    Assert::equals(new Name('static'), $value);
  }

  #[Test, Values(eval: '[ClassMemberParsingTest::$value]')]
  public function static_member_via_unqualified_current($value) {
    Assert::equals('static', $value);
  }

  #[Test, Values(eval: '[\lang\unittest\ClassMemberParsingTest::$value]')]
  public function static_member_via_fully_qualified_current($value) {
    Assert::equals('static', $value);
  }

  #[Test, Values([self::class, ClassMemberParsingTest::class, \lang\unittest\ClassMemberParsingTest::class])]
  public function class_constant_referencing_this_class($value) {
    Assert::equals(typeof($this)->literal(), $value);
  }

  #[Test, Values([Namespaced::class, \lang\unittest\fixture\Namespaced::class])]
  public function class_constant_referencing_foreign_class($value) {
    Assert::equals(
      XPClass::forName('lang.unittest.fixture.Namespaced')->literal(),
      $value
    );
  }

  #[Test, Values([\Exception::class])]
  public function class_constant_referencing_native_class($value) {
    Assert::equals('Exception', $value);
  }
}