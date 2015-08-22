<?php namespace net\xp_framework\unittest\util\collections;
 
use util\collections\Queue;
use util\NoSuchElementException;
use lang\IndexOutOfBoundsException;

class QueueTest extends \unittest\TestCase {
  private $queue;
  
  /**
   * Setup method. Creates the queue member
   *
   * @return void
   */
  public function setUp() {
    $this->queue= new Queue();
  }
      
  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue($this->queue->isEmpty());
  }

  #[@test]
  public function equalsClone() {
    $this->queue->put(new Name('green'));
    $this->assertTrue($this->queue->equals(clone($this->queue)));
  }

  #[@test]
  public function put() {
    $this->queue->put(new Name('green'));
    $this->assertFalse($this->queue->isEmpty());
    $this->assertEquals(1, $this->queue->size());
  }

  #[@test]
  public function get() {
    $color= new Name('red');
    $this->queue->put($color);
    $this->assertEquals($color, $this->queue->get());
    $this->assertTrue($this->queue->isEmpty());
  }

  #[@test, @expect(NoSuchElementException::class)]
  public function exceptionOnNoMoreElements() {
    $this->queue->get();
  }

  #[@test]
  public function peek() {
    $color= new Name('blue');
    $this->queue->put($color);
    $this->assertEquals($color, $this->queue->peek());
    $this->assertFalse($this->queue->isEmpty());
  }

  #[@test]
  public function peekReturnsNullOnNoMoreElements() {
    $this->assertNull($this->queue->peek());
  }

  #[@test]
  public function remove() {
    $color= new Name('blue');
    $this->queue->put($color);
    $this->queue->remove($color);
    $this->assertTrue($this->queue->isEmpty());
  }

  #[@test]
  public function removeReturnsWhetherDeleted() {
    $color= new Name('pink');
    $this->queue->put($color);
    $this->assertTrue($this->queue->remove($color));
    $this->assertFalse($this->queue->remove(new Name('purple')));
    $this->assertTrue($this->queue->isEmpty());
    $this->assertFalse($this->queue->remove($color));
    $this->assertFalse($this->queue->remove(new Name('purple')));
  }

  #[@test]
  public function elementAt() {
    $this->queue->put(new Name('red'));
    $this->queue->put(new Name('green'));
    $this->queue->put(new Name('blue'));
    $this->assertEquals(new Name('red'), $this->queue->elementAt(0));
    $this->assertEquals(new Name('green'), $this->queue->elementAt(1));
    $this->assertEquals(new Name('blue'), $this->queue->elementAt(2));
  }

  #[@test]
  public function iterativeUse() {
    $input= [new Name('red'), new Name('green'), new Name('blue')];
    
    // Add
    for ($i= 0, $s= sizeof($input); $i < sizeof($input); $i++) {
      $this->queue->put($input[$i]);
    }
    
    // Retrieve
    $i= 0;
    while (!$this->queue->isEmpty()) {
      $element= $this->queue->get();

      if (!$input[$i]->equals($element)) {
        $this->fail('Not equal at offset #'.$i, $element, $input[$i]);
        break;
      }
      $i++;
    }
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function elementAtIllegalOffset() {
    $this->queue->elementAt(-1);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function elementAtOffsetOutOfBounds() {
    $this->queue->put(new Name('one'));
    $this->queue->elementAt($this->queue->size() + 1);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function elementAtEmptyList() {
    $this->queue->elementAt(0);
  }
}
