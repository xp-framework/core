<?php namespace net\xp_framework\unittest\reflection;

use unittest\actions\RuntimeVersion;
use unittest\{Assert, Action, Test};

class FieldModifiersTest extends FieldsTest {

  #[Test]
  public function public_modifier() {
    Assert::equals(MODIFIER_PUBLIC, $this->field('public $fixture;')->getModifiers());
  }

  #[Test]
  public function private_modifier() {
    Assert::equals(MODIFIER_PRIVATE, $this->field('private $fixture;')->getModifiers());
  }

  #[Test]
  public function protected_modifier() {
    Assert::equals(MODIFIER_PROTECTED, $this->field('protected $fixture;')->getModifiers());
  }

  #[Test]
  public function static_modifier() {
    Assert::equals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->field('public static $fixture;')->getModifiers());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1")')]
  public function readonly_modifier() {
    Assert::equals(MODIFIER_READONLY | MODIFIER_PUBLIC, $this->field('public readonly int $fixture;')->getModifiers());
  }
}