<?php namespace net\xp_framework\unittest\util\collections;
 
use util\collections\Queue;
use lang\types\String;

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
    $this->queue->put(new String('green'));
    $this->assertTrue($this->queue->equals(clone($this->queue)));
  }

  #[@test]
  public function put() {
    $this->queue->put(new String('green'));
    $this->assertFalse($this->queue->isEmpty());
    $this->assertEquals(1, $this->queue->size());
  }

  #[@test]
  public function get() {
    $color= new String('red');
    $this->queue->put($color);
    $this->assertEquals($color, $this->queue->get());
    $this->assertTrue($this->queue->isEmpty());
  }

  #[@test, @expect('util.NoSuchElementException')]
  public function exceptionOnNoMoreElements() {
    $this->queue->get();
  }

  #[@test]
  public function peek() {
    $color= new String('blue');
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
    $color= new String('blue');
    $this->queue->put($color);
    $this->queue->remove($color);
    $this->assertTrue($this->queue->isEmpty());
  }

  #[@test]
  public function removeReturnsWhetherDeleted() {
    $color= new String('pink');
    $this->queue->put($color);
    $this->assertTrue($this->queue->remove($color));
    $this->assertFalse($this->queue->remove(new String('purple')));
    $this->assertTrue($this->queue->isEmpty());
    $this->assertFalse($this->queue->remove($color));
    $this->assertFalse($this->queue->remove(new String('purple')));
  }

  #[@test]
  public function elementAt() {
    $this->queue->put(new String('red'));
    $this->queue->put(new String('green'));
    $this->queue->put(new String('blue'));
    $this->assertEquals(new String('red'), $this->queue->elementAt(0));
    $this->assertEquals(new String('green'), $this->queue->elementAt(1));
    $this->assertEquals(new String('blue'), $this->queue->elementAt(2));
  }

  #[@test]
  public function iterativeUse() {
    $input= array(new String('red'), new String('green'), new String('blue'));
    
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

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function elementAtIllegalOffset() {
    $this->queue->elementAt(-1);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function elementAtOffsetOutOfBounds() {
    $this->queue->put(new String('one'));
    $this->queue->elementAt($this->queue->size() + 1);
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function elementAtEmptyList() {
    $this->queue->elementAt(0);
  }
}
