<?php namespace net\xp_framework\unittest\reflection;

use lang\{Type, Primitive, ArrayType, MapType, XPClass, Value};
use unittest\actions\RuntimeVersion;

class MethodReturnTypesTest extends MethodsTest {

  #[@test]
  public function return_type_defaults_to_var() {
    $this->assertEquals(Type::$VAR, $this->method('public function fixture() { }')->getReturnType());
  }

  #[@test]
  public function return_typeName_defaults_to_var() {
    $this->assertEquals('var', $this->method('public function fixture() { }')->getReturnTypeName());
  }

  #[@test]
  public function return_type_inherited() {
    $this->assertEquals(Primitive::$BOOL, $this->type()->getMethod('equals')->getReturnType());
  }

  #[@test, @values([
  #  ['/** @return void */', Type::$VOID],
  #  ['/** @return var */', Type::$VAR],
  #  ['/** @return bool */', Primitive::$BOOL],
  #  ['/** @return string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @return [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @return lang.Value */', new XPClass(Value::class)],
  #  ['/** @return Value */', new XPClass(Value::class)],
  #  ['/** @return \lang\Value */', new XPClass(Value::class)]
  #])]
  public function return_type_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture() { }')->getReturnType());
  }

  #[@test, @values([
  #  ['/** @return void */', 'void'],
  #  ['/** @return var */', 'var'],
  #  ['/** @return bool */', 'bool'],
  #  ['/** @return string[] */', 'string[]'],
  #  ['/** @return [:int] */', '[:int]'],
  #  ['/** @return lang.Value */', 'lang.Value'],
  #  ['/** @return Value */', 'lang.Value'],
  #  ['/** @return \lang\Value */', 'lang.Value'],
  #  ['/** @return self */', 'self']
  #])]
  public function return_typeName_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture() { }')->getReturnTypeName());
  }

  #[@test, @action(new RuntimeVersion('>=7.0')), @values([
  #  ['string', Primitive::$STRING],
  #  ['array', Type::$ARRAY],
  #  ['\lang\Value', new XPClass(Value::class)],
  #  ['Value', new XPClass(Value::class)]
  #])]
  public function return_type_determined_via_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture(): '.$literal.' { }')->getReturnType());
  }

  #[@test]
  public function self_return_type() {
    $fixture= $this->type('{ /** @return self */ public function fixture() { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test, @action(new RuntimeVersion('>=7.1'))]
  public function void_return_type() {
    $fixture= $this->type('{ public function fixture(): void { } }');
    $this->assertEquals(Type::$VOID, $fixture->getMethod('fixture')->getReturnType());
  }
}