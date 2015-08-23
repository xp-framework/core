<?php namespace util\collections;

use lang\Generic;
use lang\Value;
use lang\IllegalArgumentException;
use lang\IndexOutOfBoundsException;

/**
 * Resizable array list
 *
 * @test  xp://net.xp_framework.unittest.util.collections.VectorTest
 * @test  xp://net.xp_framework.unittest.util.collections.GenericsTest
 * @test  xp://net.xp_framework.unittest.util.collections.ArrayAccessTest
 * @see   xp://lang.types.ArrayList
 */
#[@generic(self= 'T', implements= ['T'])]
class Vector extends \lang\Object implements IList {
  protected static
    $iterate   = null;

  protected
    $elements  = [],
    $size      = 0;

  static function __static() {
    self::$iterate= newinstance('Iterator', [], '{
      private $i= 0, $v;
      public function on($v) { $self= new self(); $self->v= $v; return $self; }
      public function current() { return $this->v[$this->i]; }
      public function key() { return $this->i; }
      public function next() { $this->i++; }
      public function rewind() { $this->i= 0; }
      public function valid() { return $this->i < sizeof($this->v); }
    }');
  }
  
  /**
   * Constructor
   *
   * @param   T[] elements default []
   */
  #[@generic(params= 'T[]')]
  public function __construct($elements= []) {
    $this->elements= $elements;
    $this->size= sizeof($this->elements);
  }

  /**
   * Returns an iterator for use in foreach()
   *
   * @see     php://language.oop5.iterations
   * @return  php.Iterator
   */
  public function getIterator() {
    return self::$iterate->on($this->elements);
  }

  /**
   * = list[] overloading
   *
   * @param   int offset
   * @return  T
   */
  #[@generic(return= 'T')]
  public function offsetGet($offset) {
    return $this->get($offset);
  }

  /**
   * list[]= overloading
   *
   * @param   int offset
   * @param   T value
   * @throws  lang.IllegalArgumentException if key is neither numeric (set) nor NULL (add)
   */
  #[@generic(params= ', T')]
  public function offsetSet($offset, $value) {
    if (is_int($offset)) {
      $this->set($offset, $value);
    } else if (null === $offset) {
      $this->add($value);
    } else {
      throw new IllegalArgumentException('Incorrect type '.gettype($offset).' for index');
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
   */
  public function offsetUnset($offset) {
    $this->remove($offset);
  }
    
  /**
   * Returns the number of elements in this list.
   *
   * @return  int
   */
  public function size() {
    return $this->size;
  }
  
  /**
   * Tests if this list has no elements.
   *
   * @return  bool
   */
  public function isEmpty() {
    return 0 == $this->size;
  }
  
  /**
   * Adds an element to this list
   *
   * @param   T element
   * @return  T the added element
   */
  #[@generic(params= 'T', return= 'T')]
  public function add($element) {
    $this->elements[]= $element;
    $this->size++;
    return $element;
  }

  /**
   * Adds an element to this list
   *
   * @param   T[] elements
   * @return  bool TRUE if the vector was changed as a result of this operation, FALSE if not
   * @throws  lang.IllegalArgumentException
   */
  #[@generic(params= 'T[]')]
  public function addAll($elements) {
    $added= 0;
    foreach ($elements as $element) {
      $this->elements[]= $element;
      $added++;
    }
    $this->size+= $added;
    return $added > 0;
  }

  /**
   * Replaces the element at the specified position in this list with 
   * the specified element.
   *
   * @param   int index
   * @param   T element
   * @return  T the element previously at the specified position.
   * @throws  lang.IndexOutOfBoundsException
   */
  #[@generic(params= ', T', return= 'T')]
  public function set($index, $element) {
    if ($index < 0 || $index >= $this->size) {
      throw new IndexOutOfBoundsException('Offset '.$index.' out of bounds');
    }

    $orig= $this->elements[$index];
    $this->elements[$index]= $element;
    return $orig;
  }
      
  /**
   * Returns the element at the specified position in this list.
   *
   * @param   int index
   * @return  T
   * @throws  lang.IndexOutOfBoundsException if key does not exist
   */
  #[@generic(return= 'T')]
  public function get($index) {
    if ($index < 0 || $index >= $this->size) {
      throw new IndexOutOfBoundsException('Offset '.$index.' out of bounds');
    }
    return $this->elements[$index];
  }
  
  /**
   * Removes the element at the specified position in this list.
   * Shifts any subsequent elements to the left (subtracts one 
   * from their indices).
   *
   * @param   int index
   * @return  T the element that was removed from the list
   */
  #[@generic(return= 'T')]
  public function remove($index) {
    if ($index < 0 || $index >= $this->size) {
      throw new IndexOutOfBoundsException('Offset '.$index.' out of bounds');
    }

    $orig= $this->elements[$index];
    unset($this->elements[$index]);
    $this->elements= array_values($this->elements);
    $this->size--;
    return $orig;
  }
  
  /**
   * Removes all of the elements from this list. The list will be empty 
   * after this call returns.
   *
   */
  public function clear() {
    $this->elements= [];
    $this->size= 0;
  }
  
  /**
   * Returns an array of this list's elements
   *
   * @return  T[]
   */
  #[@generic(return= 'T[]')]
  public function elements() {
    return $this->elements;
  }
  
  /**
   * Checks if a value exists in this array
   *
   * @param   T element
   * @return  bool
   */
  #[@generic(params= 'T')]
  public function contains($element) {
    if ($element instanceof Generic) {
      foreach ($this->elements as $i => $compare) {
        if ($element->equals($compare)) return true;
      }
    } else if ($element instanceof Value) {
      foreach ($this->elements as $i => $compare) {
        if (0 === $element->compareTo($compare)) return true;
      }
    } else {
      foreach ($this->elements as $i => $compare) {
        if ($element === $compare) return true;
      }
    }
    return false;
  }
  
  /**
   * Searches for the first occurence of the given argument
   *
   * @param   T element
   * @return  int offset where the element was found or FALSE
   */
  #[@generic(params= 'T')]
  public function indexOf($element) {
    if ($element instanceof Generic) {
      foreach ($this->elements as $i => $compare) {
        if ($element->equals($compare)) return $i;
      }
    } else if ($element instanceof Value) {
      foreach ($this->elements as $i => $compare) {
        if (0 === $element->compareTo($compare)) return $i;
      }
    } else {
      foreach ($this->elements as $i => $compare) {
        if ($element === $compare) return $i;
      }
    }
    return false;
  }

  /**
   * Searches for the last occurence of the given argument
   *
   * @param   T element
   * @return  int offset where the element was found or FALSE
   */
  #[@generic(params= 'T')]
  public function lastIndexOf($element) {
    if ($element instanceof Generic) {
      for ($i= $this->size- 1; $i > -1; $i--) {
        if ($element->equals($this->elements[$i])) return $i;
      }
    } else if ($element instanceof Value) {
      for ($i= $this->size- 1; $i > -1; $i--) {
        if (0 === $element->compareTo($this->elements[$i])) return $i;
      }
    } else {
      for ($i= $this->size- 1; $i > -1; $i--) {
        if ($element === $this->elements[$i]) return $i;
      }
    }
    return false;
  }
  
  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    $r= nameof($this).'['.$this->size."]@{\n";
    foreach ($this->elements as $i => $element) {
      $r.= '  '.$i.': '.\xp::stringOf($element, '  ')."\n";
    } 
    return $r.'}';
  }
  
  /**
   * Checks if a specified object is equal to this object.
   *
   * @param   lang.Generic collection
   * @return  bool
   */
  public function equals($cmp) {
    if (!($cmp instanceof self) || $this->size !== $cmp->size) return false;
    
    // Compare element by element
    foreach ($this->elements as $i => $element) {
      if ($element instanceof Generic && !$element->equals($cmp->elements[$i])) return false;
      if ($element instanceof Value && 0 !== $element->compareTo($cmp->elements[$i])) return false;
      if ($element !== $this->elements[$i]) return false;
    }
    return true;
  }
}
