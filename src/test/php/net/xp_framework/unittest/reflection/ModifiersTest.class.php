<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Modifiers;

/**
 * Test the XP reflection API's Modifiers utility class
 *
 * @see   xp://lang.reflect.Modifiers
 */
class ModifiersTest extends \unittest\TestCase {

  #[@test]
  public function defaultModifier() {
    $this->assertTrue(Modifiers::isPublic(0));
  }

  #[@test]
  public function defaultModifierString() {
    $this->assertEquals('public', Modifiers::stringOf(0));
  }

  #[@test]
  public function defaultModifierNames() {
    $this->assertEquals(['public'], Modifiers::namesOf(0));
  }

  #[@test]
  public function publicModifier() {
    $this->assertTrue(Modifiers::isPublic(MODIFIER_PUBLIC));
  }

  #[@test]
  public function publicModifierString() {
    $this->assertEquals('public', Modifiers::stringOf(MODIFIER_PUBLIC));
  }

  #[@test]
  public function publicModifierNames() {
    $this->assertEquals(['public'], Modifiers::namesOf(MODIFIER_PUBLIC));
  }

  #[@test]
  public function privateModifier() {
    $this->assertTrue(Modifiers::isPrivate(MODIFIER_PRIVATE));
  }

  #[@test]
  public function privateModifierString() {
    $this->assertEquals('private', Modifiers::stringOf(MODIFIER_PRIVATE));
  }

  #[@test]
  public function privateModifierNames() {
    $this->assertEquals(['private'], Modifiers::namesOf(MODIFIER_PRIVATE));
  }

  #[@test]
  public function protectedModifier() {
    $this->assertTrue(Modifiers::isProtected(MODIFIER_PROTECTED));
  }

  #[@test]
  public function protectedModifierString() {
    $this->assertEquals('protected', Modifiers::stringOf(MODIFIER_PROTECTED));
  }

  #[@test]
  public function protectedModifierNames() {
    $this->assertEquals(['protected'], Modifiers::namesOf(MODIFIER_PROTECTED));
  }

  #[@test]
  public function abstractModifier() {
    $this->assertTrue(Modifiers::isAbstract(MODIFIER_ABSTRACT));
  }

  #[@test]
  public function abstractModifierString() {
    $this->assertEquals('public abstract', Modifiers::stringOf(MODIFIER_ABSTRACT));
  }

  #[@test]
  public function abstractModifierNames() {
    $this->assertEquals(['public', 'abstract'], Modifiers::namesOf(MODIFIER_ABSTRACT));
  }

  #[@test]
  public function finalModifier() {
    $this->assertTrue(Modifiers::isFinal(MODIFIER_FINAL));
  }

  #[@test]
  public function finalModifierString() {
    $this->assertEquals('public final', Modifiers::stringOf(MODIFIER_FINAL));
  }

  #[@test]
  public function finalModifierNames() {
    $this->assertEquals(['public', 'final'], Modifiers::namesOf(MODIFIER_FINAL));
  }

  #[@test]
  public function staticModifier() {
    $this->assertTrue(Modifiers::isStatic(MODIFIER_STATIC));
  }

  #[@test]
  public function staticModifierString() {
    $this->assertEquals('public static', Modifiers::stringOf(MODIFIER_STATIC));
  }

  #[@test]
  public function staticModifierNames() {
    $this->assertEquals(['public', 'static'], Modifiers::namesOf(MODIFIER_STATIC));
  }
}
