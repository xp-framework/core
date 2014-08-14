<?php namespace lang\types;

/**
 * Represents a mapped array
 *
 * @test   xp://net.xp_framework.unittest.core.types.ArrayMapTest
 */
class ArrayMap extends \lang\Object implements \ArrayAccess, \IteratorAggregate {
  public
    $pairs  = [],
    $size   = 0;

  /**
   * Returns a hashcode for this number
   *
   * @return  string
   */
  public function hashCode() {
    return $this->size.'{'.serialize($this->pairs);
  }
  
  /**
   * Constructor
   *
   * @param   [:var] pairs
   */
  public function __construct(array $pairs) {
    $this->pairs= $pairs;
    $this->size= sizeof($this->pairs);
  }
  
  /**
   * Returns an iterator for use in foreach()
   *
   * @see     php://language.oop5.iterations
   * @return  php.Iterator<int, var>
   */
  public function getIterator() {
    return new \ArrayIterator($this->pairs);
  }

  /**
   * = list[] overloading
   *
   * @param   int key
   * @return  var
   * @throws  lang.IndexOutOfBoundsException if key does not exist
   */
  public function offsetGet($key) {
    if (!isset($this->pairs[$key])) {
      raise('lang.IndexOutOfBoundsException', 'No element for key "'.$key.'"');
    }
    return $this->pairs[$key];
  }

  /**
   * list[]= overloading
   *
   * @param   string $key
   * @param   var $value
   * @throws  lang.IllegalArgumentException if key is neither a string (set) nor NULL (add)
   */
  public function offsetSet($key, $value) {
    if (!is_string($key)) {
      throw new \lang\IllegalArgumentException('Incorrect type '.gettype($key).' for index');
    }
    $this->pairs[$key]= $value;
  }

  /**
   * isset() overloading
   *
   * @param   string $key
   * @return  bool
   */
  public function offsetExists($key) {
    return array_key_exists($key, $this->pairs);
  }

  /**
   * unset() overloading
   *
   * @param   string $key
   */
  public function offsetUnset($key) {
    unset($this->pairs[$key]);
  }

  /**
   * Returns whether a given value exists in this list
   *
   * @param   var value
   * @return  bool
   */
  public function contains($value) {
    if (!$value instanceof \lang\Generic) {
      return in_array($value, $this->pairs, true);
    } else foreach ($this->pairs as $v) {
      if ($value->equals($v)) return true;
    }
    return false;
  }
  
  /**
   * Helper method to compare two arrays recursively
   *
   * @param   array a1
   * @param   array a2
   * @return  bool
   */
  protected function arrayequals($a1, $a2) {
    if (sizeof($a1) != sizeof($a2)) return false;

    foreach (array_keys((array)$a1) as $k) {
      switch (true) {
        case !array_key_exists($k, $a2): 
          return false;

        case is_array($a1[$k]):
          if (!$this->arrayequals($a1[$k], $a2[$k])) return false;
          break;

        case $a1[$k] instanceof \lang\Generic:
          if (!$a1[$k]->equals($a2[$k])) return false;
          break;

        case $a1[$k] !== $a2[$k]:
          return false;
      }
    }
    return true;
  }
  
  /**
   * Checks whether a given object is equal to this arraylist
   *
   * @param   lang.Object cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->arrayequals($this->pairs, $cmp->pairs);
  }
  
  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    $r= '';
    foreach ($this->pairs as $key => $value) {
      $r.= ', '.$key.' = '.\xp::stringOf($value);
    }
    return $this->getClassName().'['.sizeof($this->pairs).']@{'.substr($r, 2).'}';
  }
}
