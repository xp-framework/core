<?php namespace lang\unittest;

use io\streams\{MemoryInputStream, Streams};
use lang\{ClassCastException, IllegalArgumentException, Primitive};
use net\xp_framework\unittest\Name;
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Expect, Test, Values};

class PrimitiveTest {

  #[Test]
  public function string_primitive() {
    Assert::equals(Primitive::$STRING, Primitive::forName('string'));
  }

  #[Test]
  public function int_primitive() {
    Assert::equals(Primitive::$INT, Primitive::forName('int'));
  }

  #[Test]
  public function float_primitive() {
    Assert::equals(Primitive::$FLOAT, Primitive::forName('float'));
  }

  #[Test]
  public function bool_primitive() {
    Assert::equals(Primitive::$BOOL, Primitive::forName('bool'));
  }

  #[Test]
  public function float_primitive_double_alias() {
    Assert::equals(Primitive::$FLOAT, Primitive::forName('double'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function array_primitive() {
    Primitive::forName('array');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function non_primitive() {
    Primitive::forName('lang.Value');
  }

  /**
   * Returns instances of all types
   *
   * @param   var[] except
   * @return  var[]
   */
  public function instances($except) {
    $values= [
      [$this], [null], [new Name('Test')], [new \ReflectionClass(self::class)],
      [false], [true],
      [''], ['Hello'],
      [0], [-1],
      [0.0], [-1.5],
      [[]],
      [['one' => 'two']]
    ];

    return array_filter($values, function($value) use ($except) {
      return !in_array($value[0], $except, true);
    });
  }

  #[Test, Values(['', 'Hello'])]
  public function isAnInstanceOfString_primitive($value) {
    Assert::true(Primitive::$STRING->isInstance($value));
  }
  
  #[Test, Values(['source' => 'instances', 'args' => [['', 'Hello']]])]
  public function notInstanceOfString_primitive($value) {
    Assert::false(Primitive::$STRING->isInstance($value));
  }

  #[Test, Values([0, -1])]
  public function isAnInstanceOfInteger_primitive($value) {
    Assert::true(Primitive::$INT->isInstance($value));
  }

  #[Test, Values(['source' => 'instances', 'args' => [[0, -1]]])]
  public function notInstanceOfInteger_primitive($value) {
    Assert::false(Primitive::$INT->isInstance($value));
  }

  #[Test, Values([0.0, -1.5])]
  public function isAnInstanceOfDouble_primitive($value) {
    Assert::true(Primitive::$FLOAT->isInstance($value));
  }

  #[Test, Values(['source' => 'instances', 'args' => [[0.0, -1.5]]])]
  public function notInstanceOfDouble_primitive($value) {
    Assert::false(Primitive::$FLOAT->isInstance($value));
  }

  #[Test, Values([false, true])]
  public function isAnInstanceOfBoolean_primitive($value) {
    Assert::true(Primitive::$BOOL->isInstance($value));
  }

  #[Test, Values(['source' => 'instances', 'args' => [[false, true]]])]
  public function notInstanceOfBoolean_primitive($value) {
    Assert::false(Primitive::$BOOL->isInstance($value));
  }

  #[Test]
  public function stringIsAssignableFromString() {
    Assert::true(Primitive::$STRING->isAssignableFrom('string'));
  }

  #[Test]
  public function stringIsAssignableFromStringType() {
    Assert::true(Primitive::$STRING->isAssignableFrom(Primitive::$STRING));
  }

  #[Test]
  public function stringIsNotAssignableFromIntType() {
    Assert::false(Primitive::$STRING->isAssignableFrom(Primitive::$INT));
  }

  #[Test]
  public function stringIsNotAssignableFromClassType() {
    Assert::false(Primitive::$STRING->isAssignableFrom(typeof($this)));
  }

  #[Test]
  public function stringIsNotAssignableFromStringArray() {
    Assert::false(Primitive::$STRING->isAssignableFrom('string[]'));
  }

  #[Test]
  public function stringIsNotAssignableFromStringMap() {
    Assert::false(Primitive::$STRING->isAssignableFrom('[:string]'));
  }

  #[Test]
  public function stringIsNotAssignableFromVar() {
    Assert::false(Primitive::$STRING->isAssignableFrom('var'));
  }

  #[Test]
  public function stringIsNotAssignableFromVoid() {
    Assert::false(Primitive::$STRING->isAssignableFrom('void'));
  }

  #[Test, Values([['', ''], ['Test', 'Test'], ['', null], ['0', 0], ['-1', -1], ['0.5', 0.5], ['', false], ['1', true]])]
  public function newInstance_of_string($expected, $value) {
    Assert::equals($expected, Primitive::$STRING->newInstance($value));
  }

  #[Test, Values([[0, ''], [0, 'Test'], [2, '2'], [123, '123'], [0xFF, '0xFF'], [0755, '0755'], [0, null], [0, 0], [-1, -1], [0, 0.5], [0, false], [1, true]])]
  public function newInstance_of_int($expected, $value) {
    Assert::equals($expected, Primitive::$INT->newInstance($value));
  }

  #[Test, Values([[0.0, ''], [0.0, 'Test'], [123.0, '123'], [0.0, '0xFF'], [755.0, '0755'], [0.0, null], [0.0, 0], [-1.0, -1], [0.5, 0.5], [0.0, false]])]
  public function newInstance_of_double($expected, $value) {
    Assert::equals($expected, Primitive::$FLOAT->newInstance($value));
  }

  #[Test, Values([[false, ''], [true, 'Test'], [false, null], [false, 0], [true, -1], [true, 0.5], [false, false], [true, true]])]
  public function newInstance_of_bool($expected, $value) {
    Assert::equals($expected, Primitive::$BOOL->newInstance($value));
  }

  #[Test, Values([['', ''], ['Test', 'Test'], [null, null], ['0', 0], ['-1', -1], ['0.5', 0.5], ['', false], ['1', true]])]
  public function cast_of_string($expected, $value) {
    Assert::equals($expected, Primitive::$STRING->cast($value));
  }

  #[Test, Values([[0, ''], [0, 'Test'], [2, '2'], [123, '123'], [0xFF, '0xFF'], [0755, '0755'], [null, null], [0, 0], [-1, -1], [0, 0.5], [0, false], [1, true]])]
  public function cast_of_int($expected, $value) {
    Assert::equals($expected, Primitive::$INT->cast($value));
  }

  #[Test, Values([[0.0, ''], [0.0, 'Test'], [123.0, '123'], [0.0, '0xFF'], [755.0, '0755'], [null, null], [0.0, 0], [-1.0, -1], [0.5, 0.5], [0.0, false]])]
  public function cast_of_double($expected, $value) {
    Assert::equals($expected, Primitive::$FLOAT->cast($value));
  }

  #[Test, Values([[false, ''], [true, 'Test'], [null, null], [false, 0], [true, -1], [true, 0.5], [false, false], [true, true]])]
  public function cast_of_bool($expected, $value) {
    Assert::equals($expected, Primitive::$BOOL->cast($value));
  }

  #[Test, Expect(IllegalArgumentException::class), Values(['int', 'double', 'bool', 'string'])]
  public function cannot_create_instances_of_primitives_from_arrays($name) {
    Primitive::forName($name)->newInstance([1, 2, 3]);
  }

  #[Test, Expect(IllegalArgumentException::class), Values(['int', 'double', 'bool', 'string'])]
  public function cannot_create_instances_of_primitives_from_maps($name) {
    Primitive::forName($name)->newInstance(['one' => 'two']);
  }

  #[Test, Expect(ClassCastException::class), Values(['int', 'double', 'bool', 'string'])]
  public function cannot_cast_arrays_to_primitives($name) {
    Primitive::forName($name)->cast([1, 2, 3]);
  }

  #[Test, Expect(ClassCastException::class), Values(['int', 'double', 'bool', 'string'])]
  public function cannot_cast_maps_to_primitives($name) {
    Primitive::forName($name)->cast(['one' => 'two']);
  }
}