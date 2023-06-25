<?php namespace lang\unittest;

use unittest\{Assert, Test};

class MethodModifiersTest extends MethodsTest {

  #[Test]
  public function public_modifier() {
    Assert::equals(MODIFIER_PUBLIC, $this->method('public function fixture() { }')->getModifiers());
  }

  #[Test]
  public function private_modifier() {
    Assert::equals(MODIFIER_PRIVATE, $this->method('private function fixture() { }')->getModifiers());
  }

  #[Test]
  public function protected_modifier() {
    Assert::equals(MODIFIER_PROTECTED, $this->method('protected function fixture() { }')->getModifiers());
  }

  #[Test]
  public function final_modifier() {
    Assert::equals(MODIFIER_FINAL | MODIFIER_PUBLIC, $this->method('public final function fixture() { }')->getModifiers());
  }

  #[Test]
  public function static_modifier() {
    Assert::equals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->method('public static function fixture() { }')->getModifiers());
  }

  #[Test]
  public function abstract_modifier() {
    Assert::equals(MODIFIER_ABSTRACT | MODIFIER_PUBLIC, $this->method('public abstract function fixture();', 'abstract')->getModifiers());
  }
}