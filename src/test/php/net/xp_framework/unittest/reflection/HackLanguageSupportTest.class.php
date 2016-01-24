<?php namespace net\xp_framework\unittest\reflection;

use lang\Type;
use lang\Enum;
use lang\Primitive;
use lang\ArrayType;
use lang\MapType;
use lang\TypeUnion;
use lang\XPClass;
use lang\ElementNotFoundException;
use lang\DynamicClassLoader;
use lang\IllegalArgumentException;
use unittest\actions\VerifyThat;

/**
 * TestCase for HACK language feature support
 *
 * @see  xp://lang.Type
 */
#[@action(new VerifyThat(function() { return defined('HHVM_VERSION'); }))]
class HackLanguageSupportTest extends \unittest\TestCase {

  /**
   * Returns a fixture for integration tests
   *
   * @return lang.XPClass
   */
  private function testClass() {
    return XPClass::forName('net.xp_framework.unittest.reflection.HackLanguageSupport');
  }

  /**
   * Returns a fixture for integration tests
   *
   * @return lang.XPClass
   */
  private function enumClass() {
    return XPClass::forName('net.xp_framework.unittest.reflection.HackLanguageEnum');
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
    $this->assertEquals(new MapType('int'), Type::forName('array<HH\string, HH\int>'));
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

  #[@test, @action(new VerifyThat(function() { return version_compare(HHVM_VERSION, '3.7.0', 'ge'); }))]
  public function method_noreturn_type() {
    $this->assertEquals(Type::$VOID, $this->testClass()->getMethod('returnsNoreturn')->getReturnType());
  }

  #[@test]
  public function method_this_return_type() {
    $this->assertEquals($this->testClass(), $this->testClass()->getMethod('returnsThis')->getReturnType());
  }

  #[@test]
  public function method_self_return_type() {
    $this->assertEquals($this->testClass(), $this->testClass()->getMethod('returnsSelf')->getReturnType());
  }

  #[@test]
  public function method_num_return_type() {
    $this->assertEquals(new TypeUnion([Primitive::$INT, Primitive::$DOUBLE]), $this->testClass()->getMethod('returnsNum')->getReturnType());
  }

  #[@test]
  public function method_arraykey_return_type() {
    $this->assertEquals(new TypeUnion([Primitive::$INT, Primitive::$STRING]), $this->testClass()->getMethod('returnsArraykey')->getReturnType());
  }

  #[@test]
  public function method_int_param_type() {
    $this->assertEquals(Primitive::$INT, $this->testClass()->getMethod('returnsNothing')->getParameter(0)->getType());
  }

  #[@test]
  public function method_self_param_type() {
    $this->assertEquals($this->testClass(), $this->testClass()->getMethod('returnsSelf')->getParameter(0)->getType());
  }

  #[@test]
  public function method_int_param_type_name() {
    $this->assertEquals('int', $this->testClass()->getMethod('returnsNothing')->getParameter(0)->getTypeName());
  }

  #[@test]
  public function typed_field_type() {
    $this->assertEquals(Primitive::$BOOL, $this->testClass()->getField('typed')->getType());
  }

  #[@test]
  public function typed_field_type_name() {
    $this->assertEquals('bool', $this->testClass()->getField('typed')->getTypeName());
  }

  #[@test]
  public function untyped_field_type() {
    $this->assertEquals(Type::$VAR, $this->testClass()->getField('untyped')->getType());
  }

  #[@test]
  public function untyped_field_type_name() {
    $this->assertEquals('var', $this->testClass()->getField('untyped')->getTypeName());
  }

  #[@test]
  public function is_enum() {
    $this->assertTrue($this->enumClass()->isEnum());
  }

  #[@test]
  public function enum_values() {
    $values= [];
    foreach (Enum::valuesOf($this->enumClass()) as $value) {
      $values[$value->name()]= $value->ordinal();
    }
    $this->assertEquals(['SMALL' => 0, 'MEDIUM' => 1, 'LARGE' => 2, 'X_LARGE' => 3], $values);
  }

  #[@test]
  public function enum_valueOf() {
    $this->assertEquals(1, Enum::valueOf($this->enumClass(), 'MEDIUM')->ordinal());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function enum_valueOf_nonexistant() {
    Enum::valueOf($this->enumClass(), 'does-not-exist');
  }
}