<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\{Primitive, ClassCastException, IllegalArgumentException};
use io\streams\{Streams, MemoryInputStream};
use unittest\actions\RuntimeVersion;
use net\xp_framework\unittest\Name;

/**
 * TestCase
 *
 * @see   xp://lang.Primitive
 */
class PrimitiveTest extends TestCase {

  #[@test]
  public function stringPrimitive() {
    $this->assertEquals(Primitive::$STRING, Primitive::forName('string'));
  }

  #[@test]
  public function intPrimitive() {
    $this->assertEquals(Primitive::$INT, Primitive::forName('int'));
  }

  #[@test]
  public function doublePrimitive() {
    $this->assertEquals(Primitive::$DOUBLE, Primitive::forName('double'));
  }

  #[@test]
  public function boolPrimitive() {
    $this->assertEquals(Primitive::$BOOL, Primitive::forName('bool'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function arrayPrimitive() {
    Primitive::forName('array');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nonPrimitive() {
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

  #[@test, @values(['', 'Hello'])]
  public function isAnInstanceOfStringPrimitive($value) {
    $this->assertTrue(Primitive::$STRING->isInstance($value));
  }
  
  #[@test, @values(source= 'instances', args= [['', 'Hello']])]
  public function notInstanceOfStringPrimitive($value) {
    $this->assertFalse(Primitive::$STRING->isInstance($value));
  }

  #[@test, @values([0, -1])]
  public function isAnInstanceOfIntegerPrimitive($value) {
    $this->assertTrue(Primitive::$INT->isInstance($value));
  }

  #[@test, @values(source= 'instances', args= [[0, -1]])]
  public function notInstanceOfIntegerPrimitive($value) {
    $this->assertFalse(Primitive::$INT->isInstance($value));
  }

  #[@test, @values([0.0, -1.5])]
  public function isAnInstanceOfDoublePrimitive($value) {
    $this->assertTrue(Primitive::$DOUBLE->isInstance($value));
  }

  #[@test, @values(source= 'instances', args= [[0.0, -1.5]])]
  public function notInstanceOfDoublePrimitive($value) {
    $this->assertFalse(Primitive::$DOUBLE->isInstance($value));
  }

  #[@test, @values([false, true])]
  public function isAnInstanceOfBooleanPrimitive($value) {
    $this->assertTrue(Primitive::$BOOL->isInstance($value));
  }

  #[@test, @values(source= 'instances', args= [[false, true]])]
  public function notInstanceOfBooleanPrimitive($value) {
    $this->assertFalse(Primitive::$BOOL->isInstance($value));
  }

  #[@test]
  public function stringIsAssignableFromString() {
    $this->assertTrue(Primitive::$STRING->isAssignableFrom('string'));
  }

  #[@test]
  public function stringIsAssignableFromStringType() {
    $this->assertTrue(Primitive::$STRING->isAssignableFrom(Primitive::$STRING));
  }

  #[@test]
  public function stringIsNotAssignableFromIntType() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom(Primitive::$INT));
  }

  #[@test]
  public function stringIsNotAssignableFromClassType() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom(typeof($this)));
  }

  #[@test]
  public function stringIsNotAssignableFromStringArray() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('string[]'));
  }

  #[@test]
  public function stringIsNotAssignableFromStringMap() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('[:string]'));
  }

  #[@test]
  public function stringIsNotAssignableFromVar() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('var'));
  }

  #[@test]
  public function stringIsNotAssignableFromVoid() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('void'));
  }

  #[@test, @values([
  #  ['', ''], ['Test', 'Test'],
  #  ['', null],
  #  ['0', 0], ['-1', -1],
  #  ['0.5', 0.5],
  #  ['', false], ['1', true]
  #])]
  public function newInstance_of_string($expected, $value) {
    $this->assertEquals($expected, Primitive::$STRING->newInstance($value));
  }

  #[@test, @values([
  #  [0, ''], [0, 'Test'], [123, '123'], [0xFF, '0xFF'], [0755, '0755'],
  #  [0, null],
  #  [0, 0], [-1, -1],
  #  [0, 0.5],
  #  [0, false], [1, true]
  #])]
  public function newInstance_of_int($expected, $value) {
    $this->assertEquals($expected, Primitive::$INT->newInstance($value));
  }

  #[@test, @values([
  #  [0.0, ''], [0.0, 'Test'], [123.0, '123'], [0.0, '0xFF'], [755.0, '0755'],
  #  [0.0, null],
  #  [0.0, 0], [-1.0, -1],
  #  [0.5, 0.5],
  #  [0.0, false]
  #])]
  public function newInstance_of_double($expected, $value) {
    $this->assertEquals($expected, Primitive::$DOUBLE->newInstance($value));
  }

  #[@test, @values([
  #  [false, ''], [true, 'Test'],
  #  [false, null],
  #  [false, 0], [true, -1],
  #  [true, 0.5],
  #  [false, false], [true, true]
  #])]
  public function newInstance_of_bool($expected, $value) {
    $this->assertEquals($expected, Primitive::$BOOL->newInstance($value));
  }

  #[@test, @values([
  #  ['', ''], ['Test', 'Test'],
  #  [null, null],
  #  ['0', 0], ['-1', -1],
  #  ['0.5', 0.5],
  #  ['', false], ['1', true]
  #])]
  public function cast_of_string($expected, $value) {
    $this->assertEquals($expected, Primitive::$STRING->cast($value));
  }

  #[@test, @values([
  #  [0, ''], [0, 'Test'], [123, '123'], [0xFF, '0xFF'], [0755, '0755'],
  #  [null, null],
  #  [0, 0], [-1, -1],
  #  [0, 0.5],
  #  [0, false], [1, true]
  #])]
  public function cast_of_int($expected, $value) {
    $this->assertEquals($expected, Primitive::$INT->cast($value));
  }

  #[@test, @values([
  #  [0.0, ''], [0.0, 'Test'], [123.0, '123'], [0.0, '0xFF'], [755.0, '0755'],
  #  [null, null],
  #  [0.0, 0], [-1.0, -1],
  #  [0.5, 0.5],
  #  [0.0, false]
  #])]
  public function cast_of_double($expected, $value) {
    $this->assertEquals($expected, Primitive::$DOUBLE->cast($value));
  }

  #[@test, @values([
  #  [false, ''], [true, 'Test'],
  #  [null, null],
  #  [false, 0], [true, -1],
  #  [true, 0.5],
  #  [false, false], [true, true]
  #])]
  public function cast_of_bool($expected, $value) {
    $this->assertEquals($expected, Primitive::$BOOL->cast($value));
  }

  #[@test, @expect(IllegalArgumentException::class), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_create_instances_of_primitives_from_arrays($name) {
    Primitive::forName($name)->newInstance([1, 2, 3]);
  }

  #[@test, @expect(IllegalArgumentException::class), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_create_instances_of_primitives_from_maps($name) {
    Primitive::forName($name)->newInstance(['one' => 'two']);
  }

  #[@test, @expect(ClassCastException::class), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_cast_arrays_to_primitives($name) {
    Primitive::forName($name)->cast([1, 2, 3]);
  }

  #[@test, @expect(ClassCastException::class), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_cast_maps_to_primitives($name) {
    Primitive::forName($name)->cast(['one' => 'two']);
  }
}
