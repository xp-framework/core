<?php namespace util\collections;

use util\Objects;

/**
 * Provides key and value for iteration
 *
 * @see  xp://util.collections.HashTable
 * @test xp://net.xp_framework.unittest.util.collections.PairTest
 */
#[@generic(self= 'K, V')]
class Pair extends \lang\Object {
  #[@type('K')]
  public $key;
  #[@type('V')]
  public $value;

  /**
   * Constructor
   *
   * @param  K key
   * @param  V value
   */
  #[@generic(params= 'K, V')]
  public function __construct($key, $value) {
    $this->key= $key;
    $this->value= $value;
  }

  /**
   * Returns whether a given value is equal to this pair
   *
   * @param  var cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && Objects::equal($cmp->key, $this->key);
  }

  /**
   * Returns a hashcode for this pair
   *
   * @return string
   */
  public function hashCode() {
    return (
      HashProvider::hashOf(Objects::hashOf($this->key)) +
      HashProvider::hashOf(Objects::hashOf($this->value))
    );
  }

  /**
   * Get string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<key= '.Objects::stringOf($this->key).', value= '.Objects::stringOf($this->value).'>';
  }
}
