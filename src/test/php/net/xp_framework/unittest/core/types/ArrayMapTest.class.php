<?php namespace net\xp_framework\unittest\core\types;

use lang\types\ArrayMap;
use lang\IndexOutOfBoundsException;
use lang\IllegalArgumentException;
use net\xp_framework\unittest\Name;

/**
 * Tests the ArrayMap class
 *
 * @deprecated Wrapper types will move to their own library
 * @see  xp://lang.types.ArrayMap
 */
class ArrayMapTest extends \unittest\TestCase {

  /** @return  var[][] */
  protected function fixtures() {
    return [
      [[], 0, 'lang.types.ArrayMap[0]@{}'],
      [['key' => 'value'], 1, 'lang.types.ArrayMap[1]@{key = "value"}'],
      [['color' => 'green', 'price' => 12.99], 2, 'lang.types.ArrayMap[2]@{color = "green", price = 12.99}']
    ];
  }

  #[@test, @values('fixtures')]
  public function can_create($value) {
    new ArrayMap($value);
  }

  #[@test, @values('fixtures')]
  public function size($value, $size) {
    $this->assertEquals($size, (new ArrayMap($value))->size);
  }

  #[@test, @values('fixtures')]
  public function pairs($value) {
    $this->assertEquals($value, (new ArrayMap($value))->values);
  }

  #[@test, @values('fixtures')]
  public function iteration($value) {
    $iterated= [];
    foreach (new ArrayMap($value) as $key => $val) {
      $iterated[$key]= $val;
    }
    $this->assertEquals($value, $iterated);
  }

  #[@test, @values('fixtures')]
  public function equals_another_instance_with_same_pairs($value) {
    $this->assertEquals(new ArrayMap($value), new ArrayMap($value));
  }

  #[@test, @values([
  #  [[]],
  #  [['color' => 'value']],
  #  [['key' => null]],
  #  [['key' => 'color']],
  #  [['color' => 'green', 'price' => 12.99]]
  #])]
  public function does_not_equal_different_map($value) {
    $this->assertNotEquals(new ArrayMap(['key' => 'value']), new ArrayMap($value));
  }

  #[@test]
  public function get_value() {
    $this->assertEquals('Test', (new ArrayMap(['key' => 'Test']))['key']);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function accessing_non_existant_key_raises_an_exception() {
    (new ArrayMap([]))['key'];
  }

  #[@test]
  public function accessing_non_existant_key_via_get_returns_default() {
    $this->assertNull((new ArrayMap([]))->get('key', null));
  }

  #[@test, @values([[['key' => 'value']], [['key' => null]]])]
  public function test_existing_value($value) {
    $map= new ArrayMap($value);
    $this->assertTrue(isset($map['key']));
  }

  #[@test]
  public function test_non_existant_value() {
    $map= new ArrayMap([]);
    $this->assertFalse(isset($map['key']));
  }

  #[@test]
  public function put_value() {
    $map= new ArrayMap([]);
    $map['key']= 'Written';
    $this->assertEquals('Written', $map['key']);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function adding_values_is_forbidden() {
    $map= new ArrayMap([]);
    $map[]= 'Forbidden';
  }

  #[@test]
  public function overwrite_value() {
    $map= new ArrayMap(['key' => 'Test']);
    $map['key']= 'Overwritten';
    $this->assertEquals('Overwritten', $map['key']);
  }

  #[@test]
  public function remove_value() {
    $map= new ArrayMap(['key' => 'Test']);
    unset($map['key']);
    $this->assertFalse(isset($map['key']));
  }

  #[@test, @values('fixtures')]
  public function string_representation($value, $size, $repr) {
    $this->assertEquals($repr, (new ArrayMap($value))->toString()
    );
  }

  #[@test, @values([1, 2, 3])]
  public function int_contained_in_map_of_ints($value) {
    $this->assertTrue((new ArrayMap(['one' => 1, 'two' => 2, 'three' => 3]))->contains($value));
  }

  #[@test, @values([1, -1, 0, '', false, null])]
  public function an_empty_map_does_not_contain_anything($value) {
    $this->assertFalse((new ArrayMap([]))->contains($value));
  }

  #[@test]
  public function a_map_of_an_object_contains_the_given_object() {
    $o= new \lang\Object();
    $this->assertTrue((new ArrayMap(['key' => $o]))->contains($o));
  }

  #[@test]
  public function a_map_of_an_object_contains_the_given_value() {
    $o= new Name('Test');
    $this->assertTrue((new ArrayMap(['key' => $o]))->contains($o));
  }

  #[@test]
  public function a_map_of_an_object_does_not_contain_null() {
    $this->assertFalse((new ArrayMap(['key' => new \lang\Object()]))->contains(null));
  }

  #[@test]
  public function a_map_of_strings_does_not_contain_an_object() {
    $this->assertFalse((new ArrayMap(['T' => 'e', 's' => 't']))->contains(new \lang\Object()));
  }
}