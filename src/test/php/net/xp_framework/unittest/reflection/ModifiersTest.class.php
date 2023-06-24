<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Modifiers;
use unittest\{Assert, Test};

class ModifiersTest {

  #[Test]
  public function defaultModifier() {
    Assert::true(Modifiers::isPublic(0));
  }

  #[Test]
  public function defaultModifierString() {
    Assert::equals('public', Modifiers::stringOf(0));
  }

  #[Test]
  public function defaultModifierNames() {
    Assert::equals(['public'], Modifiers::namesOf(0));
  }

  #[Test]
  public function publicModifier() {
    Assert::true(Modifiers::isPublic(MODIFIER_PUBLIC));
  }

  #[Test]
  public function publicModifierString() {
    Assert::equals('public', Modifiers::stringOf(MODIFIER_PUBLIC));
  }

  #[Test]
  public function publicModifierNames() {
    Assert::equals(['public'], Modifiers::namesOf(MODIFIER_PUBLIC));
  }

  #[Test]
  public function privateModifier() {
    Assert::true(Modifiers::isPrivate(MODIFIER_PRIVATE));
  }

  #[Test]
  public function privateModifierString() {
    Assert::equals('private', Modifiers::stringOf(MODIFIER_PRIVATE));
  }

  #[Test]
  public function privateModifierNames() {
    Assert::equals(['private'], Modifiers::namesOf(MODIFIER_PRIVATE));
  }

  #[Test]
  public function protectedModifier() {
    Assert::true(Modifiers::isProtected(MODIFIER_PROTECTED));
  }

  #[Test]
  public function protectedModifierString() {
    Assert::equals('protected', Modifiers::stringOf(MODIFIER_PROTECTED));
  }

  #[Test]
  public function protectedModifierNames() {
    Assert::equals(['protected'], Modifiers::namesOf(MODIFIER_PROTECTED));
  }

  #[Test]
  public function abstractModifier() {
    Assert::true(Modifiers::isAbstract(MODIFIER_ABSTRACT));
  }

  #[Test]
  public function abstractModifierString() {
    Assert::equals('public abstract', Modifiers::stringOf(MODIFIER_ABSTRACT));
  }

  #[Test]
  public function abstractModifierNames() {
    Assert::equals(['public', 'abstract'], Modifiers::namesOf(MODIFIER_ABSTRACT));
  }

  #[Test]
  public function finalModifier() {
    Assert::true(Modifiers::isFinal(MODIFIER_FINAL));
  }

  #[Test]
  public function finalModifierString() {
    Assert::equals('public final', Modifiers::stringOf(MODIFIER_FINAL));
  }

  #[Test]
  public function finalModifierNames() {
    Assert::equals(['public', 'final'], Modifiers::namesOf(MODIFIER_FINAL));
  }

  #[Test]
  public function staticModifier() {
    Assert::true(Modifiers::isStatic(MODIFIER_STATIC));
  }

  #[Test]
  public function staticModifierString() {
    Assert::equals('public static', Modifiers::stringOf(MODIFIER_STATIC));
  }

  #[Test]
  public function staticModifierNames() {
    Assert::equals(['public', 'static'], Modifiers::namesOf(MODIFIER_STATIC));
  }

  #[Test]
  public function readonlyModifier() {
    Assert::true(Modifiers::isReadonly(MODIFIER_READONLY));
  }

  #[Test]
  public function readonlyModifierNames() {
    Assert::equals(['public', 'readonly'], Modifiers::namesOf(MODIFIER_READONLY));
  }
}