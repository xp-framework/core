<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Modifiers;
use unittest\Test;

/**
 * Test the XP reflection API's Modifiers utility class
 *
 * @see   xp://lang.reflect.Modifiers
 */
class ModifiersTest extends \unittest\TestCase {

  #[Test]
  public function defaultModifier() {
    $this->assertTrue(Modifiers::isPublic(0));
  }

  #[Test]
  public function defaultModifierString() {
    $this->assertEquals('public', Modifiers::stringOf(0));
  }

  #[Test]
  public function defaultModifierNames() {
    $this->assertEquals(['public'], Modifiers::namesOf(0));
  }

  #[Test]
  public function publicModifier() {
    $this->assertTrue(Modifiers::isPublic(MODIFIER_PUBLIC));
  }

  #[Test]
  public function publicModifierString() {
    $this->assertEquals('public', Modifiers::stringOf(MODIFIER_PUBLIC));
  }

  #[Test]
  public function publicModifierNames() {
    $this->assertEquals(['public'], Modifiers::namesOf(MODIFIER_PUBLIC));
  }

  #[Test]
  public function privateModifier() {
    $this->assertTrue(Modifiers::isPrivate(MODIFIER_PRIVATE));
  }

  #[Test]
  public function privateModifierString() {
    $this->assertEquals('private', Modifiers::stringOf(MODIFIER_PRIVATE));
  }

  #[Test]
  public function privateModifierNames() {
    $this->assertEquals(['private'], Modifiers::namesOf(MODIFIER_PRIVATE));
  }

  #[Test]
  public function protectedModifier() {
    $this->assertTrue(Modifiers::isProtected(MODIFIER_PROTECTED));
  }

  #[Test]
  public function protectedModifierString() {
    $this->assertEquals('protected', Modifiers::stringOf(MODIFIER_PROTECTED));
  }

  #[Test]
  public function protectedModifierNames() {
    $this->assertEquals(['protected'], Modifiers::namesOf(MODIFIER_PROTECTED));
  }

  #[Test]
  public function abstractModifier() {
    $this->assertTrue(Modifiers::isAbstract(MODIFIER_ABSTRACT));
  }

  #[Test]
  public function abstractModifierString() {
    $this->assertEquals('public abstract', Modifiers::stringOf(MODIFIER_ABSTRACT));
  }

  #[Test]
  public function abstractModifierNames() {
    $this->assertEquals(['public', 'abstract'], Modifiers::namesOf(MODIFIER_ABSTRACT));
  }

  #[Test]
  public function finalModifier() {
    $this->assertTrue(Modifiers::isFinal(MODIFIER_FINAL));
  }

  #[Test]
  public function finalModifierString() {
    $this->assertEquals('public final', Modifiers::stringOf(MODIFIER_FINAL));
  }

  #[Test]
  public function finalModifierNames() {
    $this->assertEquals(['public', 'final'], Modifiers::namesOf(MODIFIER_FINAL));
  }

  #[Test]
  public function staticModifier() {
    $this->assertTrue(Modifiers::isStatic(MODIFIER_STATIC));
  }

  #[Test]
  public function staticModifierString() {
    $this->assertEquals('public static', Modifiers::stringOf(MODIFIER_STATIC));
  }

  #[Test]
  public function staticModifierNames() {
    $this->assertEquals(['public', 'static'], Modifiers::namesOf(MODIFIER_STATIC));
  }

  #[Test]
  public function readonlyModifier() {
    $this->assertTrue(Modifiers::isReadonly(MODIFIER_READONLY));
  }

  #[Test]
  public function readonlyModifierNames() {
    $this->assertEquals(['public', 'readonly'], Modifiers::namesOf(MODIFIER_READONLY));
  }
}