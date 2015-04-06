<?php namespace util\collections;

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
    return (
      $cmp instanceof self &&
      ($cmp->key instanceof \lang\Generic ? $cmp->key->equals($this->key) : $cmp->key === $this->key) &&
      ($cmp->value instanceof \lang\Generic ? $cmp->value->equals($this->value) : $cmp->value === $this->value)
    );
  }

  /**
   * Returns a hashcode for this pair
   *
   * @return string
   */
  public function hashCode() {
    return (
      HashProvider::hashOf($this->key instanceof \lang\Generic ? $this->key->hashCode() : serialize($this->key)) +
      HashProvider::hashOf($this->value instanceof \lang\Generic ? $this->value->hashCode() : serialize($this->value))
    );
  }

  /**
   * Get string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<key= '.\xp::stringOf($this->key).', value= '.\xp::stringOf($this->value).'>';
  }
}
