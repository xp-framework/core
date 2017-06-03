<?php namespace net\xp_framework\unittest\annotations;

use net\xp_framework\unittest\annotations\fixture\Namespaced;
use lang\XPClass;

/**
 * Tests the XP Framework's annotation parsing implementation
 *
 * @see   https://github.com/xp-framework/xp-framework/pull/328
 */
class ClassMemberParsingTest extends \unittest\TestCase {
  const CONSTANT = 'local';
  protected static $value = 'static';

  #[@test, @values([self::CONSTANT])]
  public function class_constant_via_self($value) {
    $this->assertEquals('local', $value);
  }

  #[@test, @values([new Name(self::CONSTANT)])]
  public function class_constant_via_self_inside_new($value) {
    $this->assertEquals(new Name('local'), $value);
  }

  #[@test, @values([ClassMemberParsingTest::CONSTANT])]
  public function class_constant_via_unqualified_current($value) {
    $this->assertEquals('local', $value);
  }

  #[@test, @values([\net\xp_framework\unittest\annotations\ClassMemberParsingTest::CONSTANT])]
  public function class_constant_via_fully_qualified_current($value) {
    $this->assertEquals('local', $value);
  }

  #[@test, @values([Namespaced::CONSTANT])]
  public function class_constant_via_imported_classname($value) {
    $this->assertEquals('namespaced', $value);
  }

  #[@test, @values([\net\xp_framework\unittest\annotations\fixture\Namespaced::CONSTANT])]
  public function class_constant_via_fully_qualified($value) {
    $this->assertEquals('namespaced', $value);
  }

  #[@test, @values([self::$value])]
  public function static_member_via_self($value) {
    $this->assertEquals('static', $value);
  }

  #[@test, @values([new Name(self::$value)])]
  public function static_member_via_self_inside_new($value) {
    $this->assertEquals(new Name('static'), $value);
  }

  #[@test, @values([ClassMemberParsingTest::$value])]
  public function static_member_via_unqualified_current($value) {
    $this->assertEquals('static', $value);
  }

  #[@test, @values([\net\xp_framework\unittest\annotations\ClassMemberParsingTest::$value])]
  public function static_member_via_fully_qualified_current($value) {
    $this->assertEquals('static', $value);
  }

  #[@test, @values([
  #  self::class,
  #  ClassMemberParsingTest::class,
  #  \net\xp_framework\unittest\annotations\ClassMemberParsingTest::class
  #])]
  public function class_constant_referencing_this_class($value) {
    $this->assertEquals(typeof($this)->literal(), $value);
  }

  #[@test, @values([
  #  Namespaced::class,
  #  \net\xp_framework\unittest\annotations\fixture\Namespaced::class
  #])]
  public function class_constant_referencing_foreign_class($value) {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.annotations.fixture.Namespaced')->literal(),
      $value
    );
  }

  #[@test, @values([\Exception::class])]
  public function class_constant_referencing_native_class($value) {
    $this->assertEquals('Exception', $value);
  }
}
