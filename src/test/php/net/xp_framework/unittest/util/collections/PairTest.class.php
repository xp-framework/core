<?php namespace net\xp_framework\unittest\util\collections;

use util\collections\Pair;

/**
 * Test Pair class
 *
 * @see  xp://util.collections.Pair
 */
class PairTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new Pair(null, null);
  }

  #[@test]
  public function key() {
    $p= new Pair('key', null);
    $this->assertEquals('key', $p->key);
  }

  #[@test]
  public function value() {
    $p= new Pair(null, 'value');
    $this->assertEquals('value', $p->value);
  }

  #[@test]
  public function equals_same_instance() {
    $p= new Pair(null, null);
    $this->assertEquals($p, $p);
  }

  #[@test]
  public function equals_null_key_null_value() {
    $this->assertEquals(new Pair(null, null), new Pair(null, null));
  }

  #[@test]
  public function equals_primitive_key_null_value() {
    $this->assertEquals(new Pair('key', null), new Pair('key', null));
  }

  #[@test]
  public function equals_primitive_key_primitive_value() {
    $this->assertEquals(new Pair('key', 'value'), new Pair('key', 'value'));
  }

  #[@test]
  public function equals_key_instance_value_instance() {
    $this->assertEquals(
      new Pair(new Name('key'), new Name('value')),
      new Pair(new Name('key'), new Name('value'))
    );
  }

  #[@test]
  public function primitive_key_and_value_not_equal_to_null_key_and_value() {
    $this->assertNotEquals(new Pair('key', 'value'), new Pair(null, null));
  }

  #[@test]
  public function instance_key_and_value_not_equal_to_null_key_and_value() {
    $this->assertNotEquals(
      new Pair(new Name('key'), new Name('value')),
      new Pair(null, null)
    );
  }

  #[@test]
  public function pair_not_equals_to_null() {
    $this->assertNotEquals(new Pair(null, null), null);
  }

  #[@test]
  public function hashcode_of_nulls_equal() {
    $this->assertEquals(
      (new Pair(null, null))->hashCode(),
      (new Pair(null, null))->hashCode()
    );
  }

  #[@test]
  public function hashcode_of_different_keys_not_equal() {
    $this->assertNotEquals(
      (new Pair(null, null))->hashCode(),
      (new Pair('key', null))->hashCode()
    );
  }

  #[@test]
  public function hashcode_of_different_values_not_equal() {
    $this->assertNotEquals(
      (new Pair(null, null))->hashCode(),
      (new Pair(null, 'value'))->hashCode()
    );
  }
}
