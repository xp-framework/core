<?php namespace net\xp_framework\unittest\reflection;

use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\MapType;
use lang\XPClass;
use unittest\PrerequisitesNotMetError;

/**
 * TestCase for HACK language feature support
 *
 * @see    http://docs.hhvm.com/manual/en/hack.annotations.types.php
 * @see    xp://lang.Type
 */
class HackLanguageSupportTest extends \unittest\TestCase {

  /**
   * Returns a fixture for integration tests
   *
   * @return lang.XPClass
   * @throws unittest.PrerequisitesNotMetError
   */
  private function testClass() {
    if (!defined('HHVM_VERSION')) {
      throw new PrerequisitesNotMetError('Only runs inside HHVM', null, PHP_VERSION);
    }

    return XPClass::forName('net.xp_framework.unittest.reflection.HackLanguageSupport');
  }

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
  public function map_of_int_type() {
    $this->assertEquals(new MapType('int'), Type::forName('array<string, int>'));
  }

  #[@test]
  public function nullable_type() {
    $this->assertEquals(XPClass::forName('lang.Object'), Type::forName('?lang\Object'));
  }

  #[@test]
  public function method_string_return_type() {
    $this->assertEquals(Primitive::$STRING, $this->testClass()->getMethod('returnsString')->getReturnType());
  }

  #[@test]
  public function method_string_return_type_name() {
    $this->assertEquals('string', $this->testClass()->getMethod('returnsString')->getReturnTypeName());
  }

  #[@test]
  public function method_void_return_type() {
    $this->assertEquals(Type::$VOID, $this->testClass()->getMethod('returnsNothing')->getReturnType());
  }

  #[@test]
  public function method_int_param_type() {
    $this->assertEquals(Primitive::$INT, $this->testClass()->getMethod('returnsNothing')->getParameter(0)->getType());
  }

  #[@test]
  public function method_int_param_type_name() {
    $this->assertEquals('int', $this->testClass()->getMethod('returnsNothing')->getParameter(0)->getTypeName());
  }

  #[@test]
  public function class_annotations() {
    $this->assertEquals(
      ['action' => 'Actionable'],
      $this->testClass()->getAnnotations()
    );
  }

  #[@test]
  public function class_has_annotations() {
    $this->assertTrue($this->testClass()->hasAnnotations());
  }

  #[@test]
  public function method_annotations() {
    $this->assertEquals(
      ['test' => null, 'limit' => 1.0, 'expect' => ['class' => 'lang.IllegalArgumentExcepton', 'withMessage' => '/*Blam*/']],
      $this->testClass()->getMethod('testAnnotations')->getAnnotations()
    );
  }

  #[@test]
  public function method_has_annotations() {
    $this->assertTrue($this->testClass()->getMethod('testAnnotations')->hasAnnotations());
  }
}
