<?php namespace net\xp_framework\unittest\util\collections;
 
use util\collections\Stack;
use lang\types\String;

class StackTest extends \unittest\TestCase {
  private $stack;
  
  /**
   * Setup method. Creates the Stack member
   *
   * @return void
   */
  public function setUp() {
    $this->stack= new Stack();
  }

  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue($this->stack->isEmpty());
  }

  #[@test]
  public function equalsClone() {
    $this->stack->push(new String('green'));
    $this->assertTrue($this->stack->equals(clone($this->stack)));
  }

  #[@test]
  public function push() {
    $this->stack->push(new String('green'));
    $this->assertFalse($this->stack->isEmpty());
    $this->assertEquals(1, $this->stack->size());
  }

  #[@test]
  public function pop() {
    $color= new String('green');
    $this->stack->push($color);
    $this->assertEquals($color, $this->stack->pop());
    $this->assertTrue($this->stack->isEmpty());
  }

  #[@test]
  public function peek() {
    $color= new String('green');
    $this->stack->push($color);
    $this->assertEquals($color, $this->stack->peek());
    $this->assertFalse($this->stack->isEmpty());
  }

  #[@test]
  public function search() {
    $color= new String('green');
    $this->stack->push($color);
    $this->assertEquals(0, $this->stack->search($color));
    $this->assertEquals(-1, $this->stack->search(new String('non-existant')));
  }

  #[@test]
  public function elementAt() {
    $this->stack->push(new String('red'));
    $this->stack->push(new String('green'));
    $this->stack->push(new String('blue'));

    $this->assertEquals(new String('blue'), $this->stack->elementAt(0));
    $this->assertEquals(new String('green'), $this->stack->elementAt(1));
    $this->assertEquals(new String('red'), $this->stack->elementAt(2));
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function elementAtIllegalOffset() {
    $this->stack->elementAt(-1);
  }
}
