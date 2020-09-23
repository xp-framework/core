<?php namespace net\xp_framework\unittest\core;

use lang\{
  ArrayType,
  ClassCastException,
  IllegalArgumentException,
  Primitive,
  Type,
  XPClass
};
use net\xp_framework\unittest\Name;
use unittest\{Expect, Test, Values, TestCase};

class ArrayTypeTest extends TestCase {

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
    $this->assertInstanceOf(ArrayType::class, Type::forName('string[]'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function arrayTypeForName() {
    ArrayType::forName('string');
  }

  #[Test]
  public function newArrayTypeWithString() {
    $this->assertEquals(ArrayType::forName('int[]'), new ArrayType('int'));
  }

  #[Test]
  public function newArrayTypeWithTypeInstance() {
    $this->assertEquals(ArrayType::forName('int[]'), new ArrayType(Primitive::$INT));
  }

  #[Test]
  public function stringComponentType() {
    $this->assertEquals(Primitive::$STRING, ArrayType::forName('string[]')->componentType());
  }

  #[Test]
  public function objectComponentType() {
    $this->assertEquals(XPClass::forName('net.xp_framework.unittest.Name'), ArrayType::forName('net.xp_framework.unittest.Name[]')->componentType());
  }

  #[Test]
  public function varComponentType() {
    $this->assertEquals(Type::$VAR, ArrayType::forName('var[]')->componentType());
  }

  #[Test]
  public function isInstance() {
    $this->assertInstanceOf(ArrayType::forName('string[]'), ['Hello', 'World']);
  }

  #[Test]
  public function isInstanceOfName() {
    $this->assertInstanceOf('string[]', ['Hello', 'World']);
  }

  #[Test]
  public function intArrayIsNotAnInstanceOfStringArray() {
    $this->assertFalse(ArrayType::forName('string[]')->isInstance([1, 2]));
  }

  #[Test]
  public function mapIsNotAnInstanceOfArray() {
    $this->assertFalse(ArrayType::forName('var[]')->isInstance(['Hello' => 'World']));
  }

  #[Test]
  public function stringArrayAssignableFromStringArray() {
    $this->assertTrue(ArrayType::forName('string[]')->isAssignableFrom('string[]'));
  }

  #[Test]
  public function stringArrayAssignableFromStringArrayType() {
    $this->assertTrue(ArrayType::forName('string[]')->isAssignableFrom(ArrayType::forName('string[]')));
  }

  #[Test]
  public function stringArrayNotAssignableFromIntType() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom(Primitive::$INT));
  }

  #[Test]
  public function stringArrayNotAssignableFromClassType() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom(typeof($this)));
  }

  #[Test]
  public function stringArrayNotAssignableFromString() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('string'));
  }

  #[Test]
  public function stringArrayNotAssignableFromStringMap() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('[:string]'));
  }

  #[Test]
  public function stringArrayNotAssignableFromVar() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('var'));
  }

  #[Test]
  public function stringArrayNotAssignableFromVoid() {
    $this->assertFalse(ArrayType::forName('string[]')->isAssignableFrom('void'));
  }

  #[Test]
  public function varArrayAssignableFromIntArray() {
    $this->assertFalse(ArrayType::forName('var[]')->isAssignableFrom('int[]'));
  }

  #[Test, Values([[[], null], [[], []], [['Test'], ['Test']], [['0', '1', '2'], [0, 1, 2]], [['a', 'b', 'c'], ['a', 'b', 'c']]])]
  public function newInstance($expected, $value) {
    $this->assertEquals($expected, ArrayType::forName('string[]')->newInstance($value));
  }

  #[Test, Expect(IllegalArgumentException::class), Values('nonArrayValues')]
  public function newInstance_raises_exceptions_for_non_arrays($value) {
    ArrayType::forName('var[]')->newInstance($value);
  }

  #[Test, Values([[null, null], [[], []], [['Test'], ['Test']], [['0', '1', '2'], [0, 1, 2]], [['a', 'b', 'c'], ['a', 'b', 'c']]])]
  public function cast($expected, $value) {
    $this->assertEquals($expected, ArrayType::forName('string[]')->cast($value));
  }

  #[Test, Expect(ClassCastException::class), Values('nonArrayValues')]
  public function cast_raises_exceptions_for_non_arrays($value) {
    ArrayType::forName('var[]')->cast($value);
  }

  #[Test]
  public function instances_created_with_strings_and_instances_are_equal() {
    $this->assertEquals(new ArrayType('string'), new ArrayType(Primitive::$STRING));
  }
}