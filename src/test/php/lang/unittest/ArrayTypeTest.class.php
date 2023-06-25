<?php namespace lang\unittest;

use lang\{
  ArrayType,
  ClassCastException,
  IllegalArgumentException,
  Primitive,
  Type,
  XPClass
};
use net\xp_framework\unittest\Name;
use unittest\{Assert, Expect, Test, Values};

class ArrayTypeTest {

  /** @return iterable */
  private function nonArrayValues() {
    yield [0];
    yield [-1];
    yield [0.5];
    yield [''];
    yield ['Test'];
    yield [true];
    yield [false];
    yield [false];
    yield [['key' => 'color', 'value' => 'price']];
  }

  #[Test]
  public function typeForName() {
    Assert::instance(ArrayType::class, Type::forName('string[]'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function arrayTypeForName() {
    ArrayType::forName('string');
  }

  #[Test]
  public function newArrayTypeWithString() {
    Assert::equals(ArrayType::forName('int[]'), new ArrayType('int'));
  }

  #[Test]
  public function newArrayTypeWithTypeInstance() {
    Assert::equals(ArrayType::forName('int[]'), new ArrayType(Primitive::$INT));
  }

  #[Test]
  public function stringComponentType() {
    Assert::equals(Primitive::$STRING, ArrayType::forName('string[]')->componentType());
  }

  #[Test]
  public function objectComponentType() {
    Assert::equals(XPClass::forName('net.xp_framework.unittest.Name'), ArrayType::forName('net.xp_framework.unittest.Name[]')->componentType());
  }

  #[Test]
  public function varComponentType() {
    Assert::equals(Type::$VAR, ArrayType::forName('var[]')->componentType());
  }

  #[Test]
  public function isInstance() {
    Assert::instance(ArrayType::forName('string[]'), ['Hello', 'World']);
  }

  #[Test]
  public function isInstanceOfName() {
    Assert::instance('string[]', ['Hello', 'World']);
  }

  #[Test]
  public function intArrayIsNotAnInstanceOfStringArray() {
    Assert::false(ArrayType::forName('string[]')->isInstance([1, 2]));
  }

  #[Test]
  public function mapIsNotAnInstanceOfArray() {
    Assert::false(ArrayType::forName('var[]')->isInstance(['Hello' => 'World']));
  }

  #[Test]
  public function stringArrayAssignableFromStringArray() {
    Assert::true(ArrayType::forName('string[]')->isAssignableFrom('string[]'));
  }

  #[Test]
  public function stringArrayAssignableFromStringArrayType() {
    Assert::true(ArrayType::forName('string[]')->isAssignableFrom(ArrayType::forName('string[]')));
  }

  #[Test]
  public function stringArrayNotAssignableFromIntType() {
    Assert::false(ArrayType::forName('string[]')->isAssignableFrom(Primitive::$INT));
  }

  #[Test]
  public function stringArrayNotAssignableFromClassType() {
    Assert::false(ArrayType::forName('string[]')->isAssignableFrom(typeof($this)));
  }

  #[Test]
  public function stringArrayNotAssignableFromString() {
    Assert::false(ArrayType::forName('string[]')->isAssignableFrom('string'));
  }

  #[Test]
  public function stringArrayNotAssignableFromStringMap() {
    Assert::false(ArrayType::forName('string[]')->isAssignableFrom('[:string]'));
  }

  #[Test]
  public function stringArrayNotAssignableFromVar() {
    Assert::false(ArrayType::forName('string[]')->isAssignableFrom('var'));
  }

  #[Test]
  public function stringArrayNotAssignableFromVoid() {
    Assert::false(ArrayType::forName('string[]')->isAssignableFrom('void'));
  }

  #[Test]
  public function varArrayAssignableFromIntArray() {
    Assert::false(ArrayType::forName('var[]')->isAssignableFrom('int[]'));
  }

  #[Test, Values([[[], null], [[], []], [['Test'], ['Test']], [['0', '1', '2'], [0, 1, 2]], [['a', 'b', 'c'], ['a', 'b', 'c']]])]
  public function newInstance($expected, $value) {
    Assert::equals($expected, ArrayType::forName('string[]')->newInstance($value));
  }

  #[Test, Expect(IllegalArgumentException::class), Values('nonArrayValues')]
  public function newInstance_raises_exceptions_for_non_arrays($value) {
    ArrayType::forName('var[]')->newInstance($value);
  }

  #[Test, Values([[null, null], [[], []], [['Test'], ['Test']], [['0', '1', '2'], [0, 1, 2]], [['a', 'b', 'c'], ['a', 'b', 'c']]])]
  public function cast($expected, $value) {
    Assert::equals($expected, ArrayType::forName('string[]')->cast($value));
  }

  #[Test, Expect(ClassCastException::class), Values('nonArrayValues')]
  public function cast_raises_exceptions_for_non_arrays($value) {
    ArrayType::forName('var[]')->cast($value);
  }

  #[Test]
  public function instances_created_with_strings_and_instances_are_equal() {
    Assert::equals(new ArrayType('string'), new ArrayType(Primitive::$STRING));
  }
}