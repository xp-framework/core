<?php namespace util;

use lang\IndexOutOfBoundsException;

/**
 * Represents a list of bytes
 *
 * @deprecated Wrapper types will move to their own library
 * @test     xp://net.xp_framework.unittest.util.BytesTest
 */
class Bytes extends \lang\Object implements \ArrayAccess, \IteratorAggregate {
  private $iterator = null;
  private $buffer, $size;
  
  /**
   * Returns input as byte
   *
   * @param   var in
   * @return  string
   */
  protected function asByte($in) {
    return is_int($in) ? chr($in) : $in{0};
  }

  /**
   * Constructor
   *
   * @param   var initial default NULL
   * @throws  lang.IllegalArgumentException in case argument is of incorrect type.
   */
  public function __construct($initial= null) {
    if (null === $initial) {
      // Intentionally empty
    } else if (is_array($initial)) {
      $this->buffer= implode('', array_map([$this, 'asByte'], $initial));
    } else if (is_string($initial)) {
      $this->buffer= $initial;
    } else {
      throw new \lang\IllegalArgumentException('Expected either char[], int[] or string but was '.\xp::typeOf($initial));
    }
    $this->size= strlen($this->buffer);
  }

  /**
   * Returns an iterator for use in foreach()
   *
   * @see     php://language.oop5.iterations
   * @return  php.Iterator
   */
  public function getIterator() {
    if (!$this->iterator) $this->iterator= newinstance('Iterator', [$this], '{
      private $i= 0, $v;
      public function __construct($v) { $this->v= $v; }
      public function current() { $n= ord($this->v->buffer{$this->i}); return $n < 128 ? $n : $n - 256; }
      public function key() { return $this->i; }
      public function next() { $this->i++; }
      public function rewind() { $this->i= 0; }
      public function valid() { return $this->i < $this->v->size; }
    }');
    return $this->iterator;
  }

  /**
   * = list[] overloading
   *
   * @param   int offset
   * @return  lang.types.Byte 
   * @throws  lang.IndexOutOfBoundsException if offset does not exist
   */
  public function offsetGet($offset) {
    if ($offset >= $this->size || $offset < 0) {
      throw new IndexOutOfBoundsException('Offset '.$offset.' out of bounds');
    }
    $n= ord($this->buffer{$offset});
    return $n < 128 ? $n : $n - 256;
  }

  /**
   * list[]= overloading
   *
   * @param   int offset
   * @param   var value
   * @throws  lang.IllegalArgumentException if key is neither numeric (set) nor NULL (add)
   * @throws  lang.IndexOutOfBoundsException if key does not exist
   */
  public function offsetSet($offset, $value) {
    if (null === $offset) {
      $this->buffer.= $this->asByte($value);
      $this->size++;
    } else if ($offset >= $this->size || $offset < 0) {
      throw new IndexOutOfBoundsException('Offset '.$offset.' out of bounds');
    } else {
      $this->buffer{$offset}= $this->asByte($value);
    }
  }

  /**
   * isset() overloading
   *
   * @param   int offset
   * @return  bool
   */
  public function offsetExists($offset) {
    return ($offset >= 0 && $offset < $this->size);
  }

  /**
   * unset() overloading
   *
   * @param   int offset
   * @throws  lang.IndexOutOfBoundsException if offset does not exist
   */
  public function offsetUnset($offset) {
    if ($offset >= $this->size || $offset < 0) {
      throw new IndexOutOfBoundsException('Offset '.$offset.' out of bounds');
    }
    $this->buffer= (
      substr($this->buffer, 0, $offset).
      substr($this->buffer, $offset+ 1, $this->size)
    );
    $this->size--;
  }

  /**
   * Returns this byte list's size
   *
   * @return  int
   */
  public function size() {
    return $this->size;
  }

  /**
   * Returns whether a given object is equal to this object
   *
   * @param   lang.Generic cmp
   * @return  bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self && 
      $this->size === $cmp->size && 
      $this->buffer === $cmp->buffer
    );
  }

  /**
   * Returns a hashcode for this bytes object
   *
   * @return  string
   */
  public function hashCode() {
    return md5($this->buffer);
  }

  /**
   * Returns a string representation of this string.
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'('.$this->size.')@{'.addcslashes($this->buffer, "\0..\37\177..\377").'}';
  }

  /**
   * String conversion overloading. This is for use with fwrite()
   *
   * @return  string
   */
  public function __toString() {
    return $this->buffer;
  }
}
