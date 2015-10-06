<?php namespace net\xp_framework\unittest\reflection;

class FieldModifiersTest extends FieldsTest {

  #[@test]
  public function public_modifier() {
    $this->assertEquals(MODIFIER_PUBLIC, $this->field('public $fixture;')->getModifiers());
  }

  #[@test]
  public function private_modifier() {
    $this->assertEquals(MODIFIER_PRIVATE, $this->field('private $fixture;')->getModifiers());
  }

  #[@test]
  public function protected_modifier() {
    $this->assertEquals(MODIFIER_PROTECTED, $this->field('protected $fixture;')->getModifiers());
  }

  #[@test]
  public function static_modifier() {
    $this->assertEquals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->field('public static $fixture;')->getModifiers());
  }
}