<?php namespace net\xp_framework\unittest\reflection;

use lang\{Type, Primitive, ArrayType, MapType, XPClass, Value, TypeUnion};
use net\xp_framework\unittest\Name;
use unittest\actions\RuntimeVersion;

class MethodReturnTypesTest extends MethodsTest {

  /**
   * Assertion helper
   *
   * @param  lang.Type $expected
   * @param  lang.reflect.Method $method
   */
  private function assertReturnType($expected, $method) {
    $this->assertEquals($expected, $method->getReturnType(), 'type');
    $this->assertEquals($expected->getName(), $method->getReturnTypeName(), 'name');
  }

  #[@test]
  public function return_type_defaults_to_var() {
    $this->assertReturnType(Type::$VAR, $this->method('public function fixture() { }'));
  }

  #[@test]
  public function return_type_restriction_defaults_to_null() {
    $this->assertNull($this->method('public function fixture() { }')->getReturnTypeRestriction());
  }

  #[@test]
  public function return_type_restriction() {
    $this->assertEquals(
      new XPClass(Value::class),
      $this->method('public function fixture(): Value { }')->getReturnTypeRestriction()
    );
  }

  #[@test]
  public function return_type_inherited() {
    $this->assertReturnType(
      Primitive::$STRING,
      $this->type('{ }', ['extends' => [Name::class]])->getMethod('hashCode')
    );
  }

  #[@test, @values([
  #  ['/** @return void */', Type::$VOID],
  #  ['/** @return var */', Type::$VAR],
  #  ['/** @return bool */', Primitive::$BOOL],
  #  ['/** @return string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @return [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @return lang.Value */', new XPClass(Value::class)],
  #  ['/** @return Value */', new XPClass(Value::class)],
  #  ['/** @return \lang\Value */', new XPClass(Value::class)],
  #  ['/** @return string|int */', new TypeUnion([Primitive::$STRING, Primitive::$INT])],
  #])]
  public function return_type_determined_via_apidoc($apidoc, $type) {
    $this->assertReturnType($type, $this->method($apidoc.' public function fixture() { }'));
  }

  #[@test, @action(new RuntimeVersion('>=7.0')), @values([
  #  ['string', Primitive::$STRING],
  #  ['array', Type::$ARRAY],
  #  ['\lang\Value', new XPClass(Value::class)],
  #  ['Value', new XPClass(Value::class)]
  #])]
  public function return_type_determined_via_syntax($literal, $type) {
    $this->assertReturnType($type, $this->method('public function fixture(): '.$literal.' { }'));
  }

  #[@test, @action([new RuntimeVersion('>=7.1')])]
  public function void_return_type() {
    $fixture= $this->type('{ public function fixture(): void { } }');
    $this->assertEquals(Type::$VOID, $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test, @action(new RuntimeVersion('>=8.0')), @values([
  #  ['string|int', new TypeUnion([Primitive::$STRING, Primitive::$INT])],
  #  ['string|false', new TypeUnion([Primitive::$STRING, Primitive::$BOOL])],
  #])]
  public function return_type_determined_via_union_syntax($literal, $type) {
    $this->assertReturnType($type, $this->method('public function fixture(): '.$literal.' { }'));
  }

  #[@test, @values([
  #  ['/** @return string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @return [:int] */', new MapType(Primitive::$INT)],
  #  ['', Type::$ARRAY],
  #])]
  public function specific_array_type_determined_via_apidoc_if_present($apidoc, $type) {
    $this->assertReturnType($type, $this->method($apidoc.' public function fixture(): array { }'));
  }

  #[@test]
  public function special_self_return_type_via_apidoc() {
    $fixture= $this->type('{ /** @return self */ public function fixture() { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test]
  public function special_self_return_type_via_syntax() {
    $fixture= $this->type('{ public function fixture(): self { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test]
  public function special_parent_return_type_via_apidoc() {
    $fixture= $this->type('{ /** @return parent */ public function fixture() { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test]
  public function special_parent_return_type_via_syntax() {
    $fixture= $this->type('{ public function fixture(): parent { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test]
  public function special_static_return_type_in_base_class() {
    $fixture= new XPClass(Name::class);
    $this->assertEquals($fixture, $fixture->getMethod('copy')->getReturnType());
  }

  #[@test]
  public function special_static_return_type_in_inherited_class() {
    $fixture= $this->type('{ }', ['extends' => [Name::class]]);
    $this->assertEquals($fixture, $fixture->getMethod('copy')->getReturnType());
  }

  #[@test, @values([
  #  ['/** @return static */', 'static'],
  #  ['/** @return self */', 'self'],
  #  ['/** @return parent */', 'parent'],
  #])]
  public function special_typeName_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture() { }')->getReturnTypeName());
  }
}