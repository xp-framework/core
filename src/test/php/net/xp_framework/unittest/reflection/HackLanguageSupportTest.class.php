<?php namespace net\xp_framework\unittest\reflection;

use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\XPClass;

/**
 * TestCase for HACK language feature support
 *
 * @see    http://docs.hhvm.com/manual/en/hack.annotations.types.php
 * @see    xp://lang.Type
 */
class HackLanguageSupportTest extends \unittest\TestCase {

  #[@test]
  public function mixed_type() {
    $this->assertEquals(Type::$VAR, Type::forName('HH\mixed'));
  }

  #[@test]
  public function string_type() {
    $this->assertEquals(Primitive::$STRING, Type::forName('HH\string'));
  }

  #[@test]
  public function int_type() {
    $this->assertEquals(Primitive::$INT, Type::forName('HH\int'));
  }

  #[@test]
  public function double_type() {
    $this->assertEquals(Primitive::$DOUBLE, Type::forName('HH\float'));
  }

  #[@test]
  public function bool_type() {
    $this->assertEquals(Primitive::$BOOL, Type::forName('HH\bool'));
  }

  #[@test]
  public function array_of_string_type() {
    $this->assertEquals(new ArrayType('string'), Type::forName('array<HH\string>'));
  }

  #[@test]
  public function nullable_type() {
    $this->assertEquals(XPClass::forName('lang.Object'), Type::forName('?lang\Object'));
  }
}
