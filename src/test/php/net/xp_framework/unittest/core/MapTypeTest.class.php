<?php namespace net\xp_framework\unittest\core;

use lang\{
  ArrayType,
  ClassCastException,
  IllegalArgumentException,
  MapType,
  Primitive,
  Type,
  XPClass
};
use net\xp_framework\unittest\Name;
use unittest\{Expect, Test, TestCase, Values};

class MapTypeTest extends TestCase {

  /** @return iterable */
  private function nonMapValues() {
    yield [0];
    yield [-1];
    yield [0.5];
    yield [''];
    yield ['Test'];
    yield [true];
    yield [false];
    yield [false];
    yield [[1, 2, 3]];
  }


  #[Test]
  public function typeForName() {
    $this->assertInstanceOf(MapType::class, Type::forName('[:string]'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function mapTypeForPrimitive() {
    MapType::forName('string');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function mapTypeForArray() {
    MapType::forName('string[]');
  }

  #[Test]
  public function newMapTypeWithString() {
    $this->assertEquals(MapType::forName('[:int]'), new MapType('int'));
  }

  #[Test]
  public function newMapTypeWithTypeInstance() {
    $this->assertEquals(MapType::forName('[:int]'), new MapType(Primitive::$INT));
  }

  #[Test]
  public function stringComponentType() {
    $this->assertEquals(Primitive::$STRING, MapType::forName('[:string]')->componentType());
  }

  #[Test]
  public function arrayComponentType() {
    $this->assertEquals(ArrayType::forName('int[]'), MapType::forName('[:int[]]')->componentType());
  }

  #[Test]
  public function mapComponentType() {
    $this->assertEquals(MapType::forName('[:int]'), MapType::forName('[:[:int]]')->componentType());
  }

  #[Test]
  public function objectComponentType() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.Name'),
      MapType::forName('[:net.xp_framework.unittest.Name]')->componentType()
    );
  }

  #[Test]
  public function varComponentType() {
    $this->assertEquals(Type::$VAR, MapType::forName('[:var]')->componentType());
  }

  #[Test]
  public function isInstance() {
    $this->assertInstanceOf(MapType::forName('[:string]'), ['greet' => 'Hello', 'whom' => 'World']);
  }

  #[Test]
  public function isInstanceOfName() {
    $this->assertInstanceOf('[:string]', ['greet' => 'Hello', 'whom' => 'World']);
  }

  #[Test]
  public function intMapIsNotAnInstanceOfStringMap() {
    $this->assertFalse(MapType::forName('[:string]')->isInstance(['one' => 1, 'two' => 2]));
  }

  #[Test]
  public function varMap() {
    $this->assertTrue(MapType::forName('[:var]')->isInstance(['one' => 1, 'two' => 'Zwei', 'three' => new Name('Test')]));
  }

  #[Test]
  public function arrayIsNotAnInstanceOfVarMap() {
    $this->assertFalse(MapType::forName('[:var]')->isInstance([1, 2, 3]));
  }

  #[Test]
  public function stringMapAssignableFromStringMap() {
    $this->assertTrue(MapType::forName('[:string]')->isAssignableFrom('[:string]'));
  }

  #[Test]
  public function stringMapAssignableFromStringMapType() {
    $this->assertTrue(MapType::forName('[:string]')->isAssignableFrom(MapType::forName('[:string]')));
  }

  #[Test]
  public function stringMapNotAssignableFromIntType() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom(Primitive::$INT));
  }

  #[Test]
  public function stringMapNotAssignableFromClassType() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom(typeof($this)));
  }

  #[Test]
  public function stringMapNotAssignableFromString() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('string'));
  }

  #[Test]
  public function stringMapNotAssignableFromStringArray() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('string[]'));
  }

  #[Test]
  public function stringMapNotAssignableFromVar() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('var'));
  }

  #[Test]
  public function stringMapNotAssignableFromVoid() {
    $this->assertFalse(MapType::forName('[:string]')->isAssignableFrom('void'));
  }

  #[Test]
  public function varMapAssignableFromIntMap() {
    $this->assertFalse(MapType::forName('[:var]')->isAssignableFrom('[:int]'));
  }

  #[Test, Values([[[], null], [[], []], [['key' => 'Test'], ['key' => 'Test']], [['one' => '1', 'two' => '2'], ['one' => 1, 'two' => 2]]])]
  public function newInstance($expected, $value) {
    $this->assertEquals($expected, MapType::forName('[:string]')->newInstance($value));
  }

  #[Test, Expect(IllegalArgumentException::class), Values('nonMapValues')]
  public function newInstance_raises_exceptions_for_non_maps($value) {
    MapType::forName('var[]')->newInstance($value);
  }

  #[Test, Values([[null, null], [[], []], [['key' => 'Test'], ['key' => 'Test']], [['one' => '1', 'two' => '2'], ['one' => 1, 'two' => 2]]])]
  public function cast($expected, $value) {
    $this->assertEquals($expected, MapType::forName('[:string]')->cast($value));
  }

  #[Test, Expect(ClassCastException::class), Values('nonMapValues')]
  public function cast_raises_exceptions_for_non_maps($value) {
    MapType::forName('[:var]')->cast($value);
  }

  #[Test]
  public function instances_created_with_strings_and_instances_are_equal() {
    $this->assertEquals(new MapType('string'), new MapType(Primitive::$STRING));
  }
}