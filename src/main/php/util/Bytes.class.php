<?php namespace util;

use ReturnTypeWillChange, ArrayAccess, IteratorAggregate, Traversable;
use lang\{IndexOutOfBoundsException, IllegalArgumentException, Value};

/**
 * Represents a list of bytes
 *
 * @test  util.unittest.BytesTest
 */
class Bytes implements Value, ArrayAccess, IteratorAggregate {
  private $buffer, $size;
  
  /**
   * Constructor
   *
   * @param  string|self|string[]|int[]... $initial
   * @throws lang.IllegalArgumentException in case argument is of incorrect type.
   */
  public function __construct(... $initial) {
    $this->buffer= '';
    foreach ($initial as $chunk) {
      if (is_string($chunk)) {
        $this->buffer.= $chunk;
      } else if (is_array($chunk)) {
        foreach ($chunk as $value) {
          $this->buffer.= is_int($value) ? chr($value) : $value;
        }
      } else if ($chunk instanceof self) {
        $this->buffer.= $chunk->buffer;
      } else {
        throw new IllegalArgumentException('Expected either string[], int[], string or util.Bytes but was '.typeof($initial)->getName());
      }
    }
    $this->size= strlen($this->buffer);
  }

  /** Returns an iterator for use in foreach() */
  public function getIterator(): Traversable {
    for ($offset= 0; $offset < $this->size; $offset++) {
      $n= ord($this->buffer[$offset]);
      yield $n < 128 ? $n : $n - 256;
    }
  }

  /**
   * Slicing
   *
   * @param  string $slice
   * @return self
   */
  public function __invoke($slice) {
    list($start, $stop)= explode(':', $slice, 2);
    $offset= (int)$start;
    $end= (int)$stop ?: $this->size;
    return new self(substr($this->buffer, $offset, ($end < 0 ? $this->size + $end : $end) - $offset));
  }

  /**
   * = list[] overloading
   *
   * @param  int $offset
   * @return int 
   * @throws lang.IndexOutOfBoundsException if offset does not exist
   */
  #[ReturnTypeWillChange]
  public function offsetGet($offset) {
    if ($offset >= $this->size || $offset < 0) {
      throw new IndexOutOfBoundsException('Offset '.$offset.' out of bounds');
    }
    $n= ord($this->buffer[$offset]);
    return $n < 128 ? $n : $n - 256;
  }

  /**
   * list[]= overloading
   *
   * @param  ?int $offset
   * @param  int|string $value
   * @throws lang.IllegalArgumentException if key is neither numeric (set) nor NULL (add)
   * @throws lang.IndexOutOfBoundsException if key does not exist
   */
  #[ReturnTypeWillChange]
  public function offsetSet($offset, $value) {
    if (null === $offset) {
      $this->buffer.= is_int($value) ? chr($value) : $value;
      $this->size++;
    } else if ($offset >= $this->size || $offset < 0) {
      throw new IndexOutOfBoundsException('Offset '.$offset.' out of bounds');
    } else {
      $this->buffer[$offset]= is_int($value) ? chr($value) : $value;
    }
  }

  /**
   * isset() overloading
   *
   * @param  int $offset
   * @return bool
   */
  #[ReturnTypeWillChange]
  public function offsetExists($offset) {
    return ($offset >= 0 && $offset < $this->size);
  }

  /**
   * unset() overloading
   *
   * @param  int $offset
   * @throws lang.IndexOutOfBoundsException if offset does not exist
   */
  #[ReturnTypeWillChange]
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

  /** Returns this byte list's size */
  public function size(): int { return $this->size; }

  /** Returns a hashcode for this bytes object */
  public function hashCode(): string { return md5($this->buffer); }

  /** String conversion overloading */
  public function __toString(): string { return $this->buffer; }

  /** Returns whether a given object is equal to this object */
  public function compareTo($value): int {
    return $value instanceof self ? $this->buffer <=> $value->buffer : 1;
  }

  /** Returns a string representation of this bytes */
  public function toString(): string {
    return nameof($this).'('.$this->size.')@{'.addcslashes($this->buffer, "\0..\37\177..\377").'}';
  }
}
