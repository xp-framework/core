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
use unittest\{Assert, Expect, Test, Values};

class MapTypeTest {

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
    Assert::instance(MapType::class, Type::forName('[:string]'));
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
    Assert::equals(MapType::forName('[:int]'), new MapType('int'));
  }

  #[Test]
  public function newMapTypeWithTypeInstance() {
    Assert::equals(MapType::forName('[:int]'), new MapType(Primitive::$INT));
  }

  #[Test]
  public function stringComponentType() {
    Assert::equals(Primitive::$STRING, MapType::forName('[:string]')->componentType());
  }

  #[Test]
  public function arrayComponentType() {
    Assert::equals(ArrayType::forName('int[]'), MapType::forName('[:int[]]')->componentType());
  }

  #[Test]
  public function mapComponentType() {
    Assert::equals(MapType::forName('[:int]'), MapType::forName('[:[:int]]')->componentType());
  }

  #[Test]
  public function objectComponentType() {
    Assert::equals(
      XPClass::forName('net.xp_framework.unittest.Name'),
      MapType::forName('[:net.xp_framework.unittest.Name]')->componentType()
    );
  }

  #[Test]
  public function varComponentType() {
    Assert::equals(Type::$VAR, MapType::forName('[:var]')->componentType());
  }

  #[Test]
  public function isInstance() {
    Assert::instance(MapType::forName('[:string]'), ['greet' => 'Hello', 'whom' => 'World']);
  }

  #[Test]
  public function isInstanceOfName() {
    Assert::instance('[:string]', ['greet' => 'Hello', 'whom' => 'World']);
  }

  #[Test]
  public function intMapIsNotAnInstanceOfStringMap() {
    Assert::false(MapType::forName('[:string]')->isInstance(['one' => 1, 'two' => 2]));
  }

  #[Test]
  public function varMap() {
    Assert::true(MapType::forName('[:var]')->isInstance(['one' => 1, 'two' => 'Zwei', 'three' => new Name('Test')]));
  }

  #[Test]
  public function arrayIsNotAnInstanceOfVarMap() {
    Assert::false(MapType::forName('[:var]')->isInstance([1, 2, 3]));
  }

  #[Test]
  public function stringMapAssignableFromStringMap() {
    Assert::true(MapType::forName('[:string]')->isAssignableFrom('[:string]'));
  }

  #[Test]
  public function stringMapAssignableFromStringMapType() {
    Assert::true(MapType::forName('[:string]')->isAssignableFrom(MapType::forName('[:string]')));
  }

  #[Test]
  public function stringMapNotAssignableFromIntType() {
    Assert::false(MapType::forName('[:string]')->isAssignableFrom(Primitive::$INT));
  }

  #[Test]
  public function stringMapNotAssignableFromClassType() {
    Assert::false(MapType::forName('[:string]')->isAssignableFrom(typeof($this)));
  }

  #[Test]
  public function stringMapNotAssignableFromString() {
    Assert::false(MapType::forName('[:string]')->isAssignableFrom('string'));
  }

  #[Test]
  public function stringMapNotAssignableFromStringArray() {
    Assert::false(MapType::forName('[:string]')->isAssignableFrom('string[]'));
  }

  #[Test]
  public function stringMapNotAssignableFromVar() {
    Assert::false(MapType::forName('[:string]')->isAssignableFrom('var'));
  }

  #[Test]
  public function stringMapNotAssignableFromVoid() {
    Assert::false(MapType::forName('[:string]')->isAssignableFrom('void'));
  }

  #[Test]
  public function varMapAssignableFromIntMap() {
    Assert::false(MapType::forName('[:var]')->isAssignableFrom('[:int]'));
  }

  #[Test, Values([[[], null], [[], []], [['key' => 'Test'], ['key' => 'Test']], [['one' => '1', 'two' => '2'], ['one' => 1, 'two' => 2]]])]
  public function newInstance($expected, $value) {
    Assert::equals($expected, MapType::forName('[:string]')->newInstance($value));
  }

  #[Test, Expect(IllegalArgumentException::class), Values('nonMapValues')]
  public function newInstance_raises_exceptions_for_non_maps($value) {
    MapType::forName('var[]')->newInstance($value);
  }

  #[Test, Values([[null, null], [[], []], [['key' => 'Test'], ['key' => 'Test']], [['one' => '1', 'two' => '2'], ['one' => 1, 'two' => 2]]])]
  public function cast($expected, $value) {
    Assert::equals($expected, MapType::forName('[:string]')->cast($value));
  }

  #[Test, Expect(ClassCastException::class), Values('nonMapValues')]
  public function cast_raises_exceptions_for_non_maps($value) {
    MapType::forName('[:var]')->cast($value);
  }

  #[Test]
  public function instances_created_with_strings_and_instances_are_equal() {
    Assert::equals(new MapType('string'), new MapType(Primitive::$STRING));
  }
}