<?php namespace net\xp_framework\unittest\reflection;

use lang\{Type, Primitive, ArrayType, MapType, XPClass, Value};

class FieldTypeTest extends FieldsTest {

  #[@test]
  public function untyped() {
    $this->assertEquals(Type::$VAR, $this->field('public $fixture;')->getType());
  }

  #[@test, @values([
  #  ['/** @var void */', Type::$VOID],
  #  ['/** @var var */', Type::$VAR],
  #  ['/** @var bool */', Primitive::$BOOL],
  #  ['/** @var string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @var [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @var lang.Value */', new XPClass(Value::class)],
  #  ['/** @var Value */', new XPClass(Value::class)],
  #  ['/** @var \lang\Value */', new XPClass(Value::class)]
  #])]
  public function field_type_determined_via_var_tag($apidoc, $type) {
    $this->assertEquals($type, $this->field($apidoc.' public $fixture;')->getType());
  }

  #[@test, @values([
  #  ['/** @var void */', 'void'],
  #  ['/** @var var */', 'var'],
  #  ['/** @var bool */', 'bool'],
  #  ['/** @var string[] */', 'string[]'],
  #  ['/** @var [:int] */', '[:int]'],
  #  ['/** @var lang.Value */', 'lang.Value'],
  #  ['/** @var Value */', 'lang.Value'],
  #  ['/** @var \lang\Value */', 'lang.Value']
  #])]
  public function field_typeName_determined_via_var_tag($apidoc, $type) {
    $this->assertEquals($type, $this->field($apidoc.' public $fixture;')->getTypeName());
  }

  #[@test, @values([
  #  ['/** @type void */', Type::$VOID],
  #  ['/** @type var */', Type::$VAR],
  #  ['/** @type bool */', Primitive::$BOOL],
  #  ['/** @type string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @type [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @type lang.Value */', new XPClass(Value::class)],
  #  ['/** @type Value */', new XPClass(Value::class)],
  #  ['/** @type \lang\Value */', new XPClass(Value::class)]
  #])]
  public function field_type_determined_via_type_tag($apidoc, $type) {
    $this->assertEquals($type, $this->field($apidoc.' public $fixture;')->getType());
  }

  #[@test, @values([
  #  ['#[@type("void")]', Type::$VOID],
  #  ['#[@type("var")]', Type::$VAR],
  #  ['#[@type("bool")]', Primitive::$BOOL],
  #  ['#[@type("string[]")]', new ArrayType(Primitive::$STRING)],
  #  ['#[@type("[:int]")]', new MapType(Primitive::$INT)],
  #  ['#[@type("lang.Value")]', new XPClass(Value::class)]
  #])]
  public function field_type_determined_via_annotation($apidoc, $type) {
    $this->assertEquals($type, $this->field($apidoc."\npublic \$fixture;")->getType());
  }

  #[@test]
  public function self_type() {
    $fixture= $this->type('{ /** @type self */ public $fixture; }');
    $this->assertEquals($fixture, $fixture->getField('fixture')->getType());
  }
}