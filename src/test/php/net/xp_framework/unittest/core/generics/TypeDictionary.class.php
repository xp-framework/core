<?php namespace net\xp_framework\unittest\core\generics;

use lang\ElementNotFoundException as NoSuchKey;
use lang\Generic;

/**
 * Lookup map
 *
 */
#[Generic(['self' => 'V', 'parent' => 'V'])]
class TypeDictionary extends AbstractTypeDictionary {
  protected $elements= [];
  
  /**
   * Put a key/value pair
   *
   * @param   lang.Type key
   * @param   V value
   */
  #[Generic(['params' => 'lang.Type, V'])]
  public function put($key, $value) {
    $offset= $key->literal();
    $this->elements[$offset]= $value;
  } 

  /**
   * Returns a value associated with a given key
   *
   * @param   lang.Type key
   * @return  V value
   * @throws  util.NoSuchElementException
   */
  #[Generic(['params' => 'lang.Type', 'return' => 'V'])]
  public function get($key) {
    $offset= $key->literal();
    if (!isset($this->elements[$offset])) {
      throw new NoSuchKey('No such key '.$key->toString());
    }
    return $this->elements[$offset];
  }

  /**
   * Returns all values
   *
   * @return  V[] values
   */
  #[Generic(['return' => 'V[]'])]
  public function values() {
    return array_values($this->elements);
  }
}