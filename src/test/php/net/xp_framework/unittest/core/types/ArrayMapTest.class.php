<?php namespace net\xp_framework\unittest\core\types;

use unittest\TestCase;
use lang\types\ArrayMap;
use lang\IndexOutOfBoundsException;

/**
 * Tests the ArrayMap class
 *
 * @see  xp://lang.types.ArrayMap
 */
class ArrayMapTest extends TestCase {

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

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function accessing_non_existant_key_raises_an_exception() {
    (new ArrayMap([]))['key'];
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

  #[@test, @expect('lang.IllegalArgumentException')]
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
}