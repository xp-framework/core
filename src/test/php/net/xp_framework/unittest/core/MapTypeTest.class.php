<?php namespace net\xp_framework\unittest\core;

use lang\{
  ArrayType,
  ClassCastException,
  IllegalArgumentException,
  MapType,
  Object,
  Primitive,
  Type,
  XPClass
};

class MapTypeTest extends \unittest\TestCase {

  #[@test]
  public function typeForName() {
    $this->assertInstanceOf(MapType::class, Type::forName('[:string]'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function mapTypeForPrimitive() {
    MapType::forName('string');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function mapTypeForArray() {
    MapType::forName('string[]');
  }

  #[@test]
  public function newMapTypeWithString() {
    $this->assertEquals(MapType::forName('[:int]'), new MapType('int'));
  }

  #[@test]
  public function newMapTypeWithTypeInstance() {
    $this->assertEquals(MapType::forName('[:int]'), new MapType(Primitive::$INT));
  }

  #[@test]
  public function stringComponentType() {
    $this->assertEquals(Primitive::$STRING, MapType::forName('[:string]')->componentType());
  }

  #[@test]
  public function arrayComponentType() {
    $this->assertEquals(ArrayType::forName('int[]'), MapType::forName('[:int[]]')->componentType());
  }

  #[@test]
  public function mapComponentType() {
    $this->assertEquals(MapType::forName('[:int]'), MapType::forName('[:[:int]]')->componentType());
  }

  #[@test]
  public function objectComponentType() {
    $this->assertEquals(XPClass::forName('lang.Object'), MapType::forName('[:lang.Object]')->componentType());
  }

  #[@test]
  public function varComponentType() {
    $this->assertEquals(Type::$VAR, MapType::forName('[:var]')->componentType());
  }

  #[@test]
  public function isInstance() {
    $this->assertInstanceOf(MapType::forName('[:string]'), ['greet' => 'Hello', 'whom' => 'World']);
  }

  #[@test]
  public function isInstanceOfName() {
    $this->assertInstanceOf('[:string]', ['greet' => 'Hello', 'whom' => 'World']);
  }

  #[@test]
  public function intMapIsNotAnInstanceOfStringMap() {
    $this->assertFalse(MapType::forName('[:string]')->isInstance(['one' => 1, 'two' => 2]));
  }

  #[@test]
  public function varMap() {
    $this->assertTrue(MapType::forName('[:var]')->isInstance(['one' => 1, 'two' => 'Zwei', 'three' => new Object()]));
  }

  #[@test]
  public function arrayIsNotAnInstanceOfVarMap() {
    $this->assertFalse(MapType::forName('[:var]')->isInstance([1, 2, 3]));
  }

  #[@test]
  public function stringMapAssignableFromStringMap() {
    $this->assertTrue(MapType::forName('[:string]')->isAssignableFrom('[:string]'));
  }

  #[@test]
  public function stringMapAssignableFromStringMapType() {
    $this->assertTrue(MapType::forName('[:string]')->isAssignableFrom(MapType::forName('[:string]')));
  }

  #[@test]
  public function stringMapNotAssignableFromIntType() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom(Primitive::$INT));
  }

  #[@test]
  public function stringMapNotAssignableFromClassType() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom($this->getClass()));
  }

  #[@test]
  public function stringMapNotAssignableFromString() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('string'));
  }

  #[@test]
  public function stringMapNotAssignableFromStringArray() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('string[]'));
  }

  #[@test]
  public function stringMapNotAssignableFromVar() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('var'));
  }

  #[@test]
  public function stringMapNotAssignableFromVoid() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('void'));
  }

  #[@test]
  public function varMapAssignableFromIntMap() {
    $this->assertFalse(MapType::forName('[:var]')->isAssignableFrom('[:int]'));
  }

  #[@test, @values([
  #  [[], null],
  #  [[], []], [['key' => 'Test'], ['key' => 'Test']],
  #  [['one' => '1', 'two' => '2'], ['one' => 1, 'two' => 2]]
  #])]
  public function newInstance($expected, $value) {
    $this->assertEquals($expected, MapType::forName('[:string]')->newInstance($value));
  }

  #[@test, @expect(IllegalArgumentException::class), @values([
  #  0, -1, 0.5, '', 'Test', new Object(), true, false,
  #  [[0, 1, 2]]
  #])]
  public function newInstance_raises_exceptions_for_non_arrays($value) {
    MapType::forName('var[]')->newInstance($value);
  }

  #[@test, @values([
  #  [null, null],
  #  [[], []], [['key' => 'Test'], ['key' => 'Test']],
  #  [['one' => '1', 'two' => '2'], ['one' => 1, 'two' => 2]]
  #])]
  public function cast($expected, $value) {
    $this->assertEquals($expected, MapType::forName('[:string]')->cast($value));
  }

  #[@test, @expect(ClassCastException::class), @values([
  #  0, -1, 0.5, '', 'Test', new Object(), true, false,
  #  [[0, 1, 2]]
  #])]
  public function cast_raises_exceptions_for_non_arrays($value) {
    MapType::forName('[:var]')->cast($value);
  }

  #[@test]
  public function instances_created_with_strings_and_instances_are_equal() {
    $this->assertEquals(new MapType('string'), new MapType(Primitive::$STRING));
  }
}
