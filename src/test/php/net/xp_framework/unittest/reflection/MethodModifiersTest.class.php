<?php namespace net\xp_framework\unittest\reflection;

use unittest\Test;

class MethodModifiersTest extends MethodsTest {

  #[Test]
  public function public_modifier() {
    $this->assertEquals(MODIFIER_PUBLIC, $this->method('public function fixture() { }')->getModifiers());
  }

  #[Test]
  public function private_modifier() {
    $this->assertEquals(MODIFIER_PRIVATE, $this->method('private function fixture() { }')->getModifiers());
  }

  #[Test]
  public function protected_modifier() {
    $this->assertEquals(MODIFIER_PROTECTED, $this->method('protected function fixture() { }')->getModifiers());
  }

  #[Test]
  public function final_modifier() {
    $this->assertEquals(MODIFIER_FINAL | MODIFIER_PUBLIC, $this->method('public final function fixture() { }')->getModifiers());
  }

  #[Test]
  public function static_modifier() {
    $this->assertEquals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->method('public static function fixture() { }')->getModifiers());
  }

  #[Test]
  public function abstract_modifier() {
    $this->assertEquals(MODIFIER_ABSTRACT | MODIFIER_PUBLIC, $this->method('public abstract function fixture();', 'abstract')->getModifiers());
  }
}