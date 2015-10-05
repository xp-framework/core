<?php namespace net\xp_framework\unittest\reflection;

use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\MapType;
use lang\XPClass;
use lang\Value;
use unittest\actions\RuntimeVersion;

class MethodReturnTypesTest extends MethodsTest {


  #[@test]
  public function return_type_defaults_to_var() {
    $this->assertEquals(Type::$VAR, $this->method('public function fixture() { }')->getReturnType());
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
  #  ['/** @return lang.Value */', new XPClass(Value::class)]
  #])]
  public function return_type_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture() { }')->getReturnType());
  }

  #[@test, @ignore('No reflection support yet'), @action(new RuntimeVersion('>=7.0')), @values([
  #  ['string', Primitive::$STRING],
  #  ['array', Type::$ARRAY],
  #  ['\lang\Value', new XPClass(Value::class)]
  #])]
  public function return_type_determined_via_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture(): '.$literal.' { }')->getReturnType());
  }

  #[@test]
  public function self_return_type() {
    $fixture= $this->type('{ /** @return self */ public function fixture() { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }
}