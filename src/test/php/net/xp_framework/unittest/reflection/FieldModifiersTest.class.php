<?php namespace net\xp_framework\unittest\reflection;

use unittest\Test;

class FieldModifiersTest extends FieldsTest {

  #[Test]
  public function public_modifier() {
    $this->assertEquals(MODIFIER_PUBLIC, $this->field('public $fixture;')->getModifiers());
  }

  #[Test]
  public function private_modifier() {
    $this->assertEquals(MODIFIER_PRIVATE, $this->field('private $fixture;')->getModifiers());
  }

  #[Test]
  public function protected_modifier() {
    $this->assertEquals(MODIFIER_PROTECTED, $this->field('protected $fixture;')->getModifiers());
  }

  #[Test]
  public function static_modifier() {
    $this->assertEquals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->field('public static $fixture;')->getModifiers());
  }
}