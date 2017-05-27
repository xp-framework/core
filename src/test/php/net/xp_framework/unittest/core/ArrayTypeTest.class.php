<?php namespace net\xp_framework\unittest\core;

use net\xp_framework\unittest\Name;
use lang\{
  ArrayType,
  ClassCastException,
  IllegalArgumentException,
  Primitive,
  Type,
  XPClass
};

class ArrayTypeTest extends \unittest\TestCase {

  #[@test]
  public function typeForName() {
    $this->assertInstanceOf(ArrayType::class, Type::forName('string[]'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function arrayTypeForName() {
    ArrayType::forName('string');
  }

  #[@test]
  public function newArrayTypeWithString() {
    $this->assertEquals(ArrayType::forName('int[]'), new ArrayType('int'));
  }

  #[@test]
  public function newArrayTypeWithTypeInstance() {
    $this->assertEquals(ArrayType::forName('int[]'), new ArrayType(Primitive::$INT));
  }

  #[@test]
  public function stringComponentType() {
    $this->assertEquals(Primitive::$STRING, ArrayType::forName('string[]')->componentType());
  }

  #[@test]
  public function objectComponentType() {
    $this->assertEquals(XPClass::forName('net.xp_framework.unittest.Name'), ArrayType::forName('net.xp_framework.unittest.Name[]')->componentType());
  }

  #[@test]
  public function varComponentType() {
    $this->assertEquals(Type::$VAR, ArrayType::forName('var[]')->componentType());
  }

  #[@test]
  public function isInstance() {
    $this->assertInstanceOf(ArrayType::forName('string[]'), ['Hello', 'World']);
  }

  #[@test]
  public function isInstanceOfName() {
    $this->assertInstanceOf('string[]', ['Hello', 'World']);
  }

  #[@test]
  public function intArrayIsNotAnInstanceOfStringArray() {
    $this->assertFalse(ArrayType::forName('string[]')->isInstance([1, 2]));
  }

  #[@test]
  public function mapIsNotAnInstanceOfArray() {
    $this->assertFalse(ArrayType::forName('var[]')->isInstance(['Hello' => 'World']));
  }

  #[@test]
  public function stringArrayAssignableFromStringArray() {
    $this->assertTrue(ArrayType::forName('string[]')->isAssignableFrom('string[]'));
  }

  #[@test]
  public function stringArrayAssignableFromStringArrayType() {
    $this->assertTrue(ArrayType::forName('string[]')->isAssignableFrom(ArrayType::forName('string[]')));
  }

  #[@test]
  public function stringArrayNotAssignableFromIntType() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom(Primitive::$INT));
  }

  #[@test]
  public function stringArrayNotAssignableFromClassType() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom(typeof($this)));
  }

  #[@test]
  public function stringArrayNotAssignableFromString() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('string'));
  }

  #[@test]
  public function stringArrayNotAssignableFromStringMap() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('[:string]'));
  }

  #[@test]
  public function stringArrayNotAssignableFromVar() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('var'));
  }

  #[@test]
  public function stringArrayNotAssignableFromVoid() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('void'));
  }

  #[@test]
  public function varArrayAssignableFromIntArray() {
    $this->assertFalse(ArrayType::forName('var[]')->isAssignableFrom('int[]'));
  }

  #[@test, @values([
  #  [[], null],
  #  [[], []], [['Test'], ['Test']],
  #  [['0', '1', '2'], [0, 1, 2]],
  #  [['a', 'b', 'c'], ['a', 'b', 'c']]
  #])]
  public function newInstance($expected, $value) {
    $this->assertEquals($expected, ArrayType::forName('string[]')->newInstance($value));
  }

  #[@test, @expect(IllegalArgumentException::class), @values([
  #  0, -1, 0.5, '', 'Test', new Name('test'), true, false,
  #  [['key' => 'color', 'value' => 'price']]
  #])]
  public function newInstance_raises_exceptions_for_non_arrays($value) {
    ArrayType::forName('var[]')->newInstance($value);
  }

  #[@test, @values([
  #  [null, null],
  #  [[], []], [['Test'], ['Test']],
  #  [['0', '1', '2'], [0, 1, 2]],
  #  [['a', 'b', 'c'], ['a', 'b', 'c']]
  #])]
  public function cast($expected, $value) {
    $this->assertEquals($expected, ArrayType::forName('string[]')->cast($value));
  }

  #[@test, @expect(ClassCastException::class), @values([
  #  0, -1, 0.5, '', 'Test', new Name('test'), true, false,
  #  [['key' => 'color', 'value' => 'price']]
  #])]
  public function cast_raises_exceptions_for_non_arrays($value) {
    ArrayType::forName('var[]')->cast($value);
  }

  #[@test]
  public function instances_created_with_strings_and_instances_are_equal() {
    $this->assertEquals(new ArrayType('string'), new ArrayType(Primitive::$STRING));
  }
}
