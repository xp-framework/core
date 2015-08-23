<?php namespace net\xp_framework\unittest\util\collections;

use util\collections\LRUBuffer;
use lang\IllegalArgumentException;

class LRUBufferTest extends \unittest\TestCase {
  const DEFAULT_SIZE = 3;

  protected $buffer= null;
  
  /**
   * Setup method. Creates the buffer member
   *
   * @return void
   */
  public function setUp() {
    $this->buffer= new LRUBuffer(self::DEFAULT_SIZE);
  }

  #[@test]
  public function initiallyEmpty() {
    $this->assertEquals(0, $this->buffer->numElements());
  }

  #[@test]
  public function getSize() {
    $this->assertEquals(self::DEFAULT_SIZE, $this->buffer->getSize());
  }

  #[@test]
  public function add() {
    $this->buffer->add(new Name('one'));
    $this->assertEquals(1, $this->buffer->numElements());
  }

  #[@test]
  public function addReturnsVictim() {

    // We should be able to add at least as many as the buffer's size
    // elements to the LRUBuffer. Nothing should be deleted from it
    // during this loop.
    for ($i= 0, $s= $this->buffer->getSize(); $i < $s; $i++) {
      if (null === ($victim= $this->buffer->add(new Name('item #'.$i)))) continue;
      
      return $this->fail(
        'Victim '.\xp::stringOf($victim).' when inserting item #'.($i + 1).'/'.$s, 
        $victim, 
        null
      );
    }
    
    // The LRUBuffer is now "full". Next time we add something, the
    // element last recently used should be returned.
    $this->assertEquals(
      new Name('item #0'), 
      $this->buffer->add(new Name('last item'))
    );
  }
  
  /**
   * Add a specified number of strings to the buffer.
   *
   * @param   int num
   */
  protected function addElements($num) {
    for ($i= 0; $i < $num; $i++) {
      $this->buffer->add(new Name('item #'.$i));
    }
  }
  
  #[@test]
  public function bufferDoesNotGrowBeyondSize() {
    $this->addElements($this->buffer->getSize()+ 1);
    $this->assertEquals($this->buffer->getSize(), $this->buffer->numElements());
  }
 
  #[@test]
  public function update() {
  
    // Fill the LRUBuffer until its size is reached
    $this->addElements($this->buffer->getSize());
    
    // Update the first item
    $this->buffer->update(new Name('item #0'));
    
    // Now the second item should be chosen the victim when adding 
    // another element
    $this->assertEquals(
      new Name('item #1'), 
      $this->buffer->add(new Name('last item'))
    );
  }

  #[@test]
  public function setSize() {
    $this->buffer->setSize(10);
    $this->assertEquals(10, $this->buffer->getSize());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function illegalSize() {
    $this->buffer->setSize(0);
  }

  #[@test]
  public function equalsClone() {
    $this->assertTrue($this->buffer->equals(clone $this->buffer));
  }

  #[@test]
  public function doesNotEqualWithDifferentSize() {
    $this->assertFalse($this->buffer->equals(new LRUBuffer(self::DEFAULT_SIZE - 1)));
  }
 
  #[@test]
  public function doesNotEqualWithSameElements() {
    $other= new LRUBuffer(self::DEFAULT_SIZE);
    with ($string= new Name('Hello')); {
      $other->add($string);
      $this->buffer->add($string);
    }
    $this->assertFalse($this->buffer->equals($other));
  }
}
