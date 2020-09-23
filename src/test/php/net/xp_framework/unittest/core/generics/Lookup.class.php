<?php namespace net\xp_framework\unittest\core\generics;

use unittest\Generic;
use util\Objects;

/**
 * Lookup map
 */
#[Generic(self: 'K, V', parent: 'K, V')]
class Lookup extends AbstractDictionary {
  protected $size;

  #[Generic(['var' => '[:V]'])]
  protected $elements= [];
  
  /**
   * Put a key/value pairt
   *
   * @param   K key
   * @param   V value
   */
  #[Generic(params: 'K, V')]
  public function put($key, $value) {
    $this->elements[Objects::hashOf($key)]= $value;
    $this->size= sizeof($this->elements);
  } 

  /**
   * Returns a value associated with a given key
   *
   * @param   K key
   * @return  V value
   * @throws  util.NoSuchElementException
   */
  #[Generic(params: 'K', return: 'V')]
  public function get($key) {
    $offset= Objects::hashOf($key);
    if (!isset($this->elements[$offset])) {
      throw new \util\NoSuchElementException('No such key '.Objects::stringOf($key));
    }
    return $this->elements[$offset];
  }

  /**
   * Returns all values
   *
   * @return  V[] values
   */
  #[Generic(return: 'V[]')]
  public function values() {
    return array_values($this->elements);
  }

  /** @return int */
  public function size() { return $this->size; }
}