<?php namespace lang\unittest;

use lang\Generic;
use util\{NoSuchElementException, Objects};

#[Generic(self: 'K, V', parent: 'K, V')]
class Lookup extends AbstractDictionary {
  private $size= 0;

  #[Generic(['var' => '[:V]'])]
  public $elements= [];
  
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
      throw new NoSuchElementException('No such key '.Objects::stringOf($key));
    }
    return $this->elements[$offset];
  }

  /**
   * Applies a given map function to all elements in this lookup,
   * returning a new list with the mapped elements.
   */
  #[Generic(self: 'M', params: 'function(V): M', return: 'self<K, M>')]
  public function map($map) {
    $m= create("new self<$K, $M>");
    foreach ($this->elements as $hash => $element) {
      $m->elements[$hash]= $map($element);
    }
    return $m;
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