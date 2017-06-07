<?php namespace net\xp_framework\unittest\reflection;

use lang\Generic;
use lang\Value;
use lang\Object;
use lang\XPClass;
use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\MapType;
use lang\FunctionType;
use lang\ClassFormatException;
use lang\IllegalStateException;
use lang\ElementNotFoundException;
use unittest\actions\RuntimeVersion;
use unittest\actions\VerifyThat;

class MethodParametersTest extends MethodsTest {

  #[@test]
  public function parameter_type_defaults_to_var() {
    $this->assertEquals(Type::$VAR, $this->method('public function fixture($param) { }')->getParameter(0)->getType());
  }

  #[@test]
  public function parameter_typeName_defaults_to_var() {
    $this->assertEquals('var', $this->method('public function fixture($param) { }')->getParameter(0)->getTypeName());
  }

  #[@test, @values([
  #  ['/** @param var */', Type::$VAR],
  #  ['/** @param bool */', Primitive::$BOOL],
  #  ['/** @param string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @param [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @param lang.Value */', new XPClass(Value::class)],
  #  ['/** @param Value */', new XPClass(Value::class)],
  #  ['/** @param \lang\Value */', new XPClass(Value::class)]
  #])]
  public function parameter_type_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture($param) { }')->getParameter(0)->getType());
  }

  #[@test, @values([
  #  ['/** @param var */', 'var'],
  #  ['/** @param bool */', 'bool'],
  #  ['/** @param string[] */', 'string[]'],
  #  ['/** @param [:int] */', '[:int]'],
  #  ['/** @param lang.Value */', 'lang.Value'],
  #  ['/** @param Value */', 'lang.Value'],
  #  ['/** @param \lang\Value */', 'lang.Value']
  #])]
  public function parameter_typeName_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture($param) { }')->getParameter(0)->getTypeName());
  }

  #[@test, @values([
  #  ['\lang\Value', new XPClass(Value::class)],
  #  ['\lang\Object', new XPClass(Object::class)],
  #  ['Value', new XPClass(Value::class)]
  #])]
  public function parameter_type_determined_via_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)->getType());
  }

  #[@test, @action(new RuntimeVersion('>=7.0')), @values([
  #  ['string', Primitive::$STRING],
  #  ['int', Primitive::$INT],
  #  ['bool', Primitive::$BOOL],
  #  ['float', Primitive::$DOUBLE]
  #])]
  public function parameter_type_determined_via_scalar_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)->getType());
  }

  #[@test]
  public function self_parameter_type() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[@test, @expect(ClassFormatException::class)]
  public function nonexistant_type_class_parameter() {
    $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getType();
  }
  
  #[@test, @action(new VerifyThat(function() { return PHP_VERSION_ID < 70000 && !defined('HHVM_VERSION'); }))]
  public function nonexistant_name_class_parameter_before_php7() {
    $this->assertEquals(
      'var',
      $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getTypeName()
    );
  }

  #[@test, @action(new VerifyThat(function() { return PHP_VERSION_ID >= 70000 || defined('HHVM_VERSION'); }))]
  public function nonexistant_name_class_parameter_with_php7() {
    $this->assertEquals(
      'net\xp_framework\unittest\reflection\UnknownTypeRestriction',
      $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getTypeName()
    );
  }

  #[@test]
  public function unrestricted_parameter() {
    $this->assertNull($this->method('public function fixture($param) { }')->getParameter(0)->getTypeRestriction());
  }

  #[@test]
  public function self_restricted_parameter() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    $this->assertEquals(
      $fixture,
      $fixture->getMethod('fixture')->getParameter(0)->getTypeRestriction()
    );
  }

  #[@test]
  public function unrestricted_parameter_with_apidoc() {
    $this->assertEquals(
      null,
      $this->method('/** @param lang.Value */ public function fixture($param) { }')->getParameter(0)->getTypeRestriction()
    );
  }

  #[@test, @values([
  #  ['\lang\Value', new XPClass(Value::class)],
  #  ['\lang\Object', new XPClass(Object::class)],
  #  ['array', Type::$ARRAY],
  #  ['callable', Type::$CALLABLE]
  #])]
  public function type_restriction_determined_via_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)->getTypeRestriction());
  }

  #[@test, @expect(ClassFormatException::class)]
  public function nonexistant_restriction_class_parameter() {
    $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getTypeRestriction();
  }

  #[@test]
  public function zero_parameters() {
    $this->assertEquals(0, $this->method('public function fixture() { }')->numParameters());
  }

  #[@test]
  public function three_parameters() {
    $this->assertEquals(3, $this->method('public function fixture($a, $b, $c) { }')->numParameters());
  }

  #[@test]
  public function no_parameters() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getParameters());
  }

  #[@test]
  public function parameter_names() {
    $this->assertEquals(['a', 'b', 'c'], array_map(
      function($p) { return $p->getName(); },
      $this->method('public function fixture($a, $b, $c) { }')->getParameters()
    ));
  }

  #[@test, @values([-1, 0, 1])]
  public function accessing_a_parameter_via_non_existant_offset($offset) {
    $this->assertNull($this->method('public function fixture() { }')->getParameter($offset));
  }

  #[@test]
  public function annotated_parameter() {
    $this->assertTrue($this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->hasAnnotations());
  }

  #[@test]
  public function parameter_annotated_with_test_has_test_annotation() {
    $this->assertTrue($this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->hasAnnotation('test'));
  }

  #[@test]
  public function parameter_annotated_with_test_has_no_limit_annotation() {
    $this->assertFalse($this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->hasAnnotation('limit'));
  }

  #[@test]
  public function annotations_of_parameter_annotated_with_test() {
    $this->assertEquals(['test' => 'value'], $this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->getAnnotations());
  }

  #[@test]
  public function test_annotation_of_parameter_annotated_with_test() {
    $this->assertEquals('value', $this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->getAnnotation('test'));
  }

  #[@test]
  public function un_annotated_parameter_has_no_annotations() {
    $this->assertFalse($this->method('public function fixture($param) { }')->getParameter(0)->hasAnnotations());
  }

  #[@test]
  public function un_annotated_parameter_annotations_are_empty() {
    $this->assertEquals([], $this->method('public function fixture($param) { }')->getParameter(0)->getAnnotations());
  }

  #[@test, @expect(class= ElementNotFoundException::class, withMessage= 'Annotation "test" does not exist')]
  public function cannot_get_test_annotation_for_un_annotated_parameter() {
    $this->method('public function fixture($param) { }')->getParameter(0)->getAnnotation('test');
  }

  #[@test]
  public function required_parameter() {
    $this->assertFalse($this->method('public function fixture($param) { }')->getParameter(0)->isOptional());
  }

  #[@test]
  public function optional_parameter() {
    $this->assertTrue($this->method('public function fixture($param= true) { }')->getParameter(0)->isOptional());
  }

  #[@test, @expect(class= IllegalStateException::class, withMessage= 'Parameter "param" has no default value')]
  public function required_parameter_does_not_have_default_value() {
    $this->method('public function fixture($param) { }')->getParameter(0)->getDefaultValue();
  }

  #[@test]
  public function optional_parameters_default_value() {
    $this->assertEquals(true, $this->method('public function fixture($param= true) { }')->getParameter(0)->getDefaultValue());
  }

  #[@test]
  public function vararg_parameters_default_value() {
    $this->assertEquals(null, $this->method('public function fixture(... $param) { }')->getParameter(0)->getDefaultValue());
  }

  #[@test, @values([
  #  ['/** @param string */ function fixture($a)', 'lang.reflect.Parameter<lang.Primitive<string> a>'],
  #  ['/** @param lang.Value */ function fixture($a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'],
  #  ['/** @param \lang\Value */ function fixture($a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'],
  #  ['function fixture(\lang\Value $a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'],
  #  ['/** @param var[] */ function fixture($a)', 'lang.reflect.Parameter<lang.ArrayType<var[]> a>'],
  #  ['/** @param function(string): int */ function fixture($a)', 'lang.reflect.Parameter<lang.FunctionType<(function(string): int)> a>'],
  #  ['/** @param bool */ function fixture($a= true)', 'lang.reflect.Parameter<lang.Primitive<bool> a= true>']
  #])]
  public function parameter_representations($declaration, $expected) {
    $this->assertEquals($expected, $this->method($declaration.' { }')->getParameter(0)->toString());
  }

  #[@test, @action(new RuntimeVersion('>=7.0'))]
  public function variadic_via_syntax_with_type() {
    $param= $this->method('function fixture(string... $args) { }')->getParameter(0);
    $this->assertEquals(
      ['variadic' => true, 'optional' => true, 'type' => Primitive::$STRING],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }

  #[@test, @action(new RuntimeVersion('>=5.6'))]
  public function variadic_via_syntax() {
    $param= $this->method('function fixture(... $args) { }')->getParameter(0);
    $this->assertEquals(
      ['variadic' => true, 'optional' => true, 'type' => Type::$VAR],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }

  #[@test]
  public function variadic_via_apidoc() {
    $param= $this->method('/** @param var... $args */ function fixture($args= null) { }')->getParameter(0);
    $this->assertEquals(
      ['variadic' => true, 'optional' => true, 'type' => Type::$VAR],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }
}