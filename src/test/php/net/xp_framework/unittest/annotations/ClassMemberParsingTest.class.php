<?php namespace net\xp_framework\unittest\annotations;

use lang\XPClass;
use net\xp_framework\unittest\annotations\fixture\Namespaced;
use unittest\Assert;
use unittest\{Test, TestCase, Values};

/**
 * Tests the XP Framework's annotation parsing implementation
 *
 * @see   https://github.com/xp-framework/xp-framework/pull/328
 */
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

  #[Test, Values([\net\xp_framework\unittest\annotations\ClassMemberParsingTest::CONSTANT])]
  public function class_constant_via_fully_qualified_current($value) {
    Assert::equals('local', $value);
  }

  #[Test, Values([Namespaced::CONSTANT])]
  public function class_constant_via_imported_classname($value) {
    Assert::equals('namespaced', $value);
  }

  #[Test, Values([\net\xp_framework\unittest\annotations\fixture\Namespaced::CONSTANT])]
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

  #[Test, Values(eval: '[\net\xp_framework\unittest\annotations\ClassMemberParsingTest::$value]')]
  public function static_member_via_fully_qualified_current($value) {
    Assert::equals('static', $value);
  }

  #[Test, Values([self::class, ClassMemberParsingTest::class, \net\xp_framework\unittest\annotations\ClassMemberParsingTest::class])]
  public function class_constant_referencing_this_class($value) {
    Assert::equals(typeof($this)->literal(), $value);
  }

  #[Test, Values([Namespaced::class, \net\xp_framework\unittest\annotations\fixture\Namespaced::class])]
  public function class_constant_referencing_foreign_class($value) {
    Assert::equals(
      XPClass::forName('net.xp_framework.unittest.annotations.fixture.Namespaced')->literal(),
      $value
    );
  }

  #[Test, Values([\Exception::class])]
  public function class_constant_referencing_native_class($value) {
    Assert::equals('Exception', $value);
  }
}