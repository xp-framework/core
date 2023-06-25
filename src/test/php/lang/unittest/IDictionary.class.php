<?php namespace lang\unittest;

use lang\Generic;

#[Generic(self: 'K, V')]
interface IDictionary {
 
  /**
   * Put a key/value pair
   *
   * @param   K key
   * @param   V value
   */
  #[Generic(params: 'K, V')]
  public function put($key, $value);

  /**
   * Returns a value associated with a given key
   *
   * @param   K key
   * @return  V value
   * @throws  util.NoSuchElementException
   */
  #[Generic(params: 'K', return: 'V')]
  public function get($key);

  /**
   * Returns all values
   *
   * @return  V[] values
   */
  #[Generic(return: 'V[]')]
  public function values();
}