<?php namespace net\xp_framework\unittest\util;

use lang\Value;
use net\xp_framework\unittest\Name;
use unittest\{Test, TestCase, Values};
use util\Objects;

/**
 * TestCase for Objects class
 *
 * @see  xp://util.Objects
 */
class ObjectsTest extends TestCase {
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
      [new ValueObject('')],
      [new ValueObject('Test')],
      [new Name('')]
    ];
  }

  /** @return  var[][] */
  public function natives() {
    return [
      [new \ReflectionClass(self::class)],
      [new \StdClass()]
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
      $this->natives(),
      [[function() { return 'Test'; }]]
    );
  }

  /**
   * Filters values() method
   *
   * @param   var exclude
   * @return  php.Iterator
   */
  public function valuesExcept($exclude) {
    foreach ($this->values as $value) {
      if ($value[0] !== $exclude) yield $value;
    }
  }

  #[Test, Values('values')]
  public function value_is_equal_to_self($val) {
    $this->assertTrue(Objects::equal($val, $val));
  }

  #[Test, Values(eval: '[[new ValueObject("")], [new ValueObject("Test")]]')]
  public function objects_with_equal_methods_are_equal_to_clones_of_themselves($val) {
    $this->assertTrue(Objects::equal($val, clone $val));
  }

  #[Test]
  public function natives_with_equal_members_are_equal() {
    $this->assertTrue(Objects::equal(new \ReflectionClass(self::class), new \ReflectionClass(self::class)));
  }

  #[Test]
  public function natives_with_equal_members_but_different_types_are_not_equal() {
    $parent= new \ReflectionClass(self::class);
    $inherited= newinstance(\ReflectionClass::class, [self::class]);
    $this->assertFalse(Objects::equal($parent, $inherited), 'parent, inherited');
    $this->assertFalse(Objects::equal($inherited, $parent), 'inherited, parent');
  }

  #[Test]
  public function natives_with_different_members_are_not_equal() {
    $this->assertFalse(Objects::equal(new \ReflectionClass(self::class), new \ReflectionClass(parent::class)));
  }

  #[Test]
  public function natives_are_not_equal_to_maps_with_same_members() {
    $this->assertFalse(Objects::equal((object)['name' => self::class], ['name' => self::class]));
  }

  #[Test]
  public function natives_are_not_equal_to_instances_with_same_members() {
    $this->assertFalse(Objects::equal((object)['name' => self::class], new \ReflectionClass(self::class)));
  }

  #[Test, Values(['source' => 'valuesExcept', 'args' => [null]])]
  public function null_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(null, $val));
  }

  #[Test, Values(['source' => 'valuesExcept', 'args' => [false]])]
  public function false_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(false, $val));
  }

  #[Test, Values(['source' => 'valuesExcept', 'args' => [true]])]
  public function true_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(true, $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function int_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(6100, $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function double_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(6100.0, $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function string_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal('More power', $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function array_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal([4, 5, 6], $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function hash_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(['color' => 'blue'], $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function object_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(new class() { }, $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function string_instance_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(new ValueObject('Binford 6100: More Power!'), $val));
  }

  #[Test, Values(['source' => 'values'])]
  public function value_not_equal_to_other_values($val) {
    $this->assertFalse(Objects::equal(new Name('Binford 6100: More Power!'), $val));
  }

  #[Test]
  public function differently_ordered_arrays_not_equal() {
    $this->assertFalse(Objects::equal([1, 2, 3], [3, 2, 1]));
  }

  #[Test]
  public function differently_ordered_hashes_are_equal() {
    $this->assertTrue(Objects::equal(
      ['price' => 12.99, 'color' => 'blue'],
      ['color' => 'blue', 'price' => 12.99]
    ));
  }

  #[Test, Values([[1, 1, 0], [0, 1, -1], [1, 0, 1], [1.0, 1.0, 0], [0.0, 1.0, -1], [1.0, 0.0, 1], ['a', 'a', 0], ['a', 'b', -1], ['b', 'a', 1], [true, true, 0], [false, false, 0], [false, true, -1], [true, false, 1], [[], [], 0], [[1, 2, 3], [1, 2, 3, 4], -1], [[1, 2, 3], [1, 2], 1]])]
  public function compare_two_of($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare($a, $b));
  }

  #[Test, Values([['a', 'a', 0], ['a', 'b', -1], ['b', 'a', 1],])]
  public function compare_values($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare(new Name($a), new Name($b)));
  }

  #[Test, Values([['a', 'a', 0], ['a', 'b', -1], ['b', 'a', 1],])]
  public function compare_objects($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare(new ValueObject($a), new ValueObject($b)));
  }

  #[Test, Values(eval: '[[new ValueObject("")], [new ValueObject("Test")]]')]
  public function compare_objects_to_clones_of_themselves($val) {
    $this->assertEquals(0, Objects::compare($val, clone $val));
  }

  #[Test, Values([[self::class, self::class, 0], [self::class, parent::class, -1], [parent::class, self::class, 1]])]
  public function compare_natives($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare(new \ReflectionClass($a), new \ReflectionClass($b)));
  }

  #[Test]
  public function compare_natives_with_equal_members_but_different_types() {
    $parent= new \ReflectionClass(self::class);
    $inherited= newinstance(\ReflectionClass::class, [self::class]);
    $this->assertEquals(1, Objects::compare($parent, $inherited), 'parent, inherited');
    $this->assertEquals(1, Objects::compare($inherited, $parent), 'inherited, parent');
  }

  #[Test]
  public function compare_natives_to_maps_with_same_members() {
    $this->assertEquals(1, Objects::compare((object)['name' => self::class], ['name' => self::class]));
  }

  #[Test]
  public function compare_natives_to_instances_with_same_members() {
    $this->assertEquals(1, Objects::compare((object)['name' => self::class], new \ReflectionClass(self::class)));
  }

  #[Test, Values([[[], [1], -1], [['color' => 'green'], ['color' => 'red'], -1], [[1], [1], 0], [[], [], 0], [[1, 2, 3], [1, 2, 3], 0], [['color' => 'green'], ['color' => 'green'], 0], [['color' => 'green'], [1], 1], [[1], ['color' => 'green'], 1], [['color' => 'green'], ['key' => 'value'], 1], [['key' => 'value'], ['color' => 'green'], 1], [[1], [], 1], [[1, 2, 3], [1, 2], 1]])]
  public function compare_arrays($a, $b, $expected) {
    $this->assertEquals($expected, Objects::compare($a, $b));
  }

  #[Test, Values([[null, 'null'], [true, 'true'], [false, 'false'], [-1, '-1'], [0, '0'], [1, '1'], [-1.0, '-1'], [0.0, '0'], [1.0, '1'], [6.1, '6.1'], ['', '""'], ['Test', '"Test"'], ['"Hello World"', '""Hello World""'], [[], '[]'], [[1, 2, 3], '[1, 2, 3]']])]
  public function stringOf($val, $expected) {
    $this->assertEquals($expected, Objects::stringOf($val));
  }

  #[Test]
  public function stringOf_single_pair_map() {
    $this->assertEquals("[key => \"value\"]", Objects::stringOf(['key' => 'value']));
  }

  #[Test]
  public function stringOf_single_pair_map_with_indentation() {
    $this->assertEquals("[key => [\n  a => 1\n  b => 2\n]]", Objects::stringOf(['key' => ['a' => 1, 'b' => 2]]));
  }

  #[Test]
  public function stringOf_map() {
    $this->assertEquals("[\n  a => 1\n  b => 2\n]", Objects::stringOf(['a' => 1, 'b' => 2]));
  }

  #[Test]
  public function stringOf_function() {
    $this->assertEquals('<function()>',  Objects::stringOf(function() { }));
  }

  #[Test, Values('objects')]
  public function stringOf_calls_toString_on_objects($val) {
    $this->assertEquals($val->toString(), Objects::stringOf($val));
  }

  #[Test]
  public function stringOf_resource() {
    $this->assertTrue((bool)preg_match('/resource\(type= stream, id= [0-9]+\)/', Objects::stringOf(STDIN)));
  }

  #[Test]
  public function stringOf_native() {
    $this->assertEquals(
      "stdClass {\n  name => \"net\\xp_framework\\unittest\\util\\ObjectsTest\"\n}",
      Objects::stringOf((object)['name' => self::class])
    );
  }

  #[Test]
  public function array_with_recursion_representation() {
    $a= [];
    $a[0]= 'Outer array';
    $a[1]= [];
    $a[1][0]= 'Inner array';
    $a[1][1]= &$a;
    $this->assertEquals(
      '["Outer array", ["Inner array", ->{:recursion:}]]',
      Objects::stringOf($a)
    );
  }

  #[Test]
  public function object_with_recursion_representation() {
    $o= new \StdClass();
    $o->child= new \StdClass();
    $o->child->parent= $o;
    $this->assertEquals(
      "stdClass {\n  child => stdClass {\n    parent => ->{:recursion:}\n  }\n}",
      Objects::stringOf($o)
    );
  }

  #[Test]
  public function twice_the_same_object_inside_array_not_recursion() {
    $test= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 1; }
      public function compareTo($value) { return 1; }
    };
    $this->assertEquals(
      "[\n  a => Test\n  b => Test\n]", 
      Objects::stringOf(['a' => $test, 'b' => $test])
    );
  }
  
  #[Test]
  public function twice_the_same_object_with_huge_hashcode_inside_array_not_recursion() {
    $test= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 1; }
      public function compareTo($value) { return 1; }
    };
    $this->assertEquals(
      "[\n  a => Test\n  b => Test\n]", 
      Objects::stringOf(['a' => $test, 'b' => $test])
    );
  }

  #[Test]
  public function toString_calling_xp_stringOf_does_not_loop_forever() {
    $test= new class() implements Value {
      public function toString() { return Objects::stringOf($this); }
      public function hashCode() { return 1; }
      public function compareTo($value) { return 1; }
    };
    $this->assertEquals(
      nameof($test)." {\n}",
      Objects::stringOf($test)
    );
  }

  #[Test]
  public function repeated_calls_to_xp_stringOf_yield_same_result() {
    $test= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 1; }
      public function compareTo($value) { return 1; }
    };
    $stringRep= $test->toString();
    $this->assertEquals(
      [$stringRep, $stringRep],
      [Objects::stringOf($test), Objects::stringOf($test)]
    );
  }

  #[Test]
  public function closure_inside_object_does_not_raise_serialization_exception() {
    $instance= new class(function($a, $b) { }) {
      public $closure;
      public function __construct($closure) { $this->closure= $closure; }
    };
    Objects::stringOf($instance);
  }

  #[Test]
  public function closure_inside_array_does_not_raise_serialization_exception() {
    Objects::stringOf([function($a, $b) { }]);
  }

  #[Test]
  public function null_hash() {
    $this->assertEquals('N;', Objects::hashOf(null));
  }

  #[Test, Values('primitives')]
  public function hashOf_calls_serialize_on_primitives($val) {
    $this->assertEquals(serialize($val), Objects::hashOf($val));
  }

  #[Test, Values('arrays')]
  public function hashOf_on_arrays($val, $expected) {
    $this->assertEquals($expected, Objects::hashOf($val));
  }

  #[Test, Values('maps')]
  public function hashOf_on_maps($val, $expected) {
    $this->assertEquals($expected, Objects::hashOf($val));
  }

  #[Test, Values('objects')]
  public function hashOf_calls_hashCode_on_objects($val) {
    $this->assertEquals((string)$val->hashCode(), Objects::hashOf($val));
  }

  #[Test, Values('natives')]
  public function hashOf_calls_spl_object_hash_on_natives($val) {
    $this->assertEquals(spl_object_hash($val), Objects::hashOf($val));
  }

  #[Test]
  public function function_hash() {
    $this->assertEquals(spl_object_hash(self::$func), Objects::hashOf(self::$func));
  }
}