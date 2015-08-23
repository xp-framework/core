<?php namespace net\xp_framework\unittest\util;

use util\Objects;
use lang\Object;
use net\xp_framework\unittest\Name;

/**
 * TestCase for Objects class
 *
 * @see  xp://util.Objects
 */
class ObjectsTest extends \unittest\TestCase {
  private static $func;

  static function __static() {
    self::$func= function() { return 'Test'; };
  }

  /** @return  var[][] */
  public function primitives() {
    return [
      [false], [true],
      [1], [0], [-1], [PHP_INT_MAX], [-PHP_INT_MAX -1],
      [1.0], [0.5], [-6.1],
      [''], ['String'], ["\0"]
    ];
  }

  /** @return  var[][] */
  public function arrays() {
    return [
      [[], ''],
      [[1, 2, 3], '|0:i:1;|1:i:2;|2:i:3;'],
      [[null, null], '|0:N;|1:N;'],
      [[['Nested'], ['Array']], '|0:|0:s:6:"Nested";|1:|0:s:5:"Array";'],
      [[self::$func], '|0:'.spl_object_hash(self::$func)]
    ];
  }

  /** @return  var[][] */
  public function maps() {
    return [
      [['one' => 'two'], '|one:s:3:"two";'],
      [['func' => self::$func], '|func:'.spl_object_hash(self::$func)]
    ];
  }

  /** @return  var[][] */
  public function objects() {
    return [
      [$this],
      [new Object()],
      [new ValueObject('')],
      [new ValueObject('Test')],
      [new Name('')]
    ];
  }

  /** @return  var[][] */
  public function values() {
    return array_merge(
      [null],
      $this->primitives(),
      $this->arrays(),
      $this->maps(),
      $this->objects(),
      [[function() { return 'Test'; }]]
    );
  }

  /**
   * Filters values() method
   *
   * @param   var exclude
   * @return  var[]
   */
  public function valuesExcept($exclude) {
    return array_filter($this->values(), function($value) use($exclude) {
      return $value[0] !== $exclude;
    });
  }

  #[@test, @values('values')]
  public function value_is_equal_to_self($val) {
    $this->assertTrue(Objects::equal($val, $val));
  }

  #[@test, @values(source= 'valuesExcept', args= [null])]
  public function null_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(null, $val));
  }

  #[@test, @values(source= 'valuesExcept', args= [false])]
  public function false_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(false, $val));
  }

  #[@test, @values(source= 'valuesExcept', args= [true])]
  public function true_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(true, $val));
  }

  #[@test, @values(source= 'values')]
  public function int_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(6100, $val));
  }

  #[@test, @values(source= 'values')]
  public function double_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(6100.0, $val));
  }

  #[@test, @values(source= 'values')]
  public function string_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal('More power', $val));
  }

  #[@test, @values(source= 'values')]
  public function array_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal([4, 5, 6], $val));
  }

  #[@test, @values(source= 'values')]
  public function hash_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(['color' => 'blue'], $val));
  }

  #[@test, @values(source= 'values')]
  public function object_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(new Object(), $val));
  }

  #[@test, @values(source= 'values')]
  public function string_instance_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(new ValueObject('Binford 6100: More Power!'), $val));
  }

  #[@test, @values(source= 'values')]
  public function value_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(new Name('Binford 6100: More Power!'), $val));
  }

  #[@test]
  public function differently_ordered_arrays_not_equal() {
    $this->assertFalse(Objects::equal([1, 2, 3], [3, 2, 1]));
  }

  #[@test]
  public function differently_ordered_hashes_are_equal() {
    $this->assertTrue(Objects::equal(
      ['price' => 12.99, 'color' => 'blue'],
      ['color' => 'blue', 'price' => 12.99]
    ));
  }

  #[@test, @values([
  #  [1, 1, 0], [0, 1, -1], [1, 0, 1],
  #  [1.0, 1.0, 0], [0.0, 1.0, -1], [1.0, 0.0, 1],
  #  ['a', 'a', 0], ['a', 'b', -1], ['b', 'a', 1],
  #  [true, true, 0], [false, false, 0], [false, true, -1], [true, false, 1],
  #  [[], [], 0], [[1, 2, 3], [1, 2, 3, 4], -1], [[1, 2, 3], [1, 2], 1]
  #])]
  public function compare_two_of($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare($a, $b));
  }

  #[@test, @values([
  #  ['a', 'a', 0],
  #  ['a', 'b', -1],
  #  ['b', 'a', 1],
  #])]
  public function compare_values($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare(new Name($a), new Name($b)));
  }

  #[@test, @values([
  #  ['a', 'a', 0],
  #  ['a', 'b', -1],
  #  ['b', 'a', 1],
  #])]
  public function compare_objects($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare(new ValueObject($a), new ValueObject($b)));
  }

  #[@test]
  public function null_string_without_default() {
    $this->assertEquals('', Objects::stringOf(null));
  }

  #[@test]
  public function null_string_with_default() {
    $this->assertEquals('default-value', Objects::stringOf(null, 'default-value'));
  }

  #[@test, @values('primitives')]
  public function stringOf_calls_xpStringOf_on_primitives($val) {
    $this->assertEquals(\xp::stringOf($val), Objects::stringOf($val));
  }

  #[@test, @values('arrays')]
  public function stringOf_calls_xpStringOf_on_arrays($val) {
    $this->assertEquals(\xp::stringOf($val), Objects::stringOf($val));
  }

  #[@test, @values('maps')]
  public function stringOf_calls_xpStringOf_on_maps($val) {
    $this->assertEquals(\xp::stringOf($val), Objects::stringOf($val));
  }

  #[@test, @values('objects')]
  public function stringOf_calls_toString_on_objects($val) {
    $this->assertEquals($val->toString(), Objects::stringOf($val));
  }

  #[@test]
  public function null_hash() {
    $this->assertEquals('N;', Objects::hashOf(null));
  }

  #[@test, @values('primitives')]
  public function hashOf_calls_serialize_on_primitives($val) {
    $this->assertEquals(serialize($val), Objects::hashOf($val));
  }

  #[@test, @values('arrays')]
  public function hashOf_calls_serialize_on_arrays($val, $expected) {
    $this->assertEquals($expected, Objects::hashOf($val));
  }

  #[@test, @values('maps')]
  public function hashOf_calls_serialize_on_maps($val, $expected) {
    $this->assertEquals($expected, Objects::hashOf($val));
  }

  #[@test, @values('objects')]
  public function hashOf_calls_hashCode_on_objects($val) {
    $this->assertEquals($val->hashCode(), Objects::hashOf($val));
  }

  #[@test]
  public function function_hash() {
    $this->assertEquals(spl_object_hash(self::$func), Objects::hashOf(self::$func));
  }
}