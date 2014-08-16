<?php namespace lang\types;

/**
 * Represents a mapped array
 *
 * @test   xp://net.xp_framework.unittest.core.types.ArrayMapTest
 */
class ArrayMap extends \lang\Object implements \ArrayAccess, \IteratorAggregate {
  public
    $values = [],
    $size   = 0;

  /**
   * Returns a hashcode for this number
   *
   * @return  string
   */
  public function hashCode() {
    return $this->size.'{'.serialize($this->values);
  }
  
  /**
   * Constructor
   *
   * @param   [:var] values
   */
  public function __construct(array $values) {
    $this->values= $values;
    $this->size= sizeof($this->values);
  }
  
  /**
   * Returns an iterator for use in foreach()
   *
   * @see     php://language.oop5.iterations
   * @return  php.Iterator<int, var>
   */
  public function getIterator() {
    return new \ArrayIterator($this->values);
  }

  /**
   * = list[] overloading
   *
   * @param   int key
   * @return  var
   * @throws  lang.IndexOutOfBoundsException if key does not exist
   */
  public function offsetGet($key) {
    if (!isset($this->values[$key])) {
      raise('lang.IndexOutOfBoundsException', 'No element for key "'.$key.'"');
    }
    return $this->values[$key];
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
    $this->values[$key]= $value;
  }

  /**
   * isset() overloading
   *
   * @param   string $key
   * @return  bool
   */
  public function offsetExists($key) {
    return array_key_exists($key, $this->values);
  }

  /**
   * unset() overloading
   *
   * @param   string $key
   */
  public function offsetUnset($key) {
    unset($this->values[$key]);
  }

  /**
   * Returns whether a given value exists in this list
   *
   * @param   var value
   * @return  bool
   */
  public function contains($value) {
    if (!$value instanceof \lang\Generic) {
      return in_array($value, $this->values, true);
    } else foreach ($this->values as $v) {
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
    return $cmp instanceof self && $this->arrayequals($this->values, $cmp->values);
  }
  
  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    $r= '';
    foreach ($this->values as $key => $value) {
      $r.= ', '.$key.' = '.\xp::stringOf($value);
    }
    return $this->getClassName().'['.sizeof($this->values).']@{'.substr($r, 2).'}';
  }
}
