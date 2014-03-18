<?php namespace net\xp_framework\unittest\util\collections;
 
use unittest\TestCase;
use util\collections\HashSet;
use lang\types\String;

/**
 * Test HashSet class
 *
 * @see   xp://util.collections.HashSet
 */
class HashSetTest extends TestCase {
  public $set= null;
  
  /**
   * Setup method. Creates the set member
   */
  public function setUp() {
    $this->set= new HashSet();
  }

  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue($this->set->isEmpty());
  }

  #[@test]
  public function equalsClone() {
    $this->set->add(new String('green'));
    $this->assertTrue($this->set->equals(clone($this->set)));
  }
 
  #[@test]
  public function equalsOtherSetWithSameContents() {
    $other= new HashSet();
    $this->set->add(new String('color'));
    $other->add(new String('color'));
    $this->assertTrue($this->set->equals($other));
  }

  #[@test]
  public function doesNotEqualSetWithDifferentContents() {
    $other= new HashSet();
    $this->set->add(new String('blue'));
    $other->add(new String('yellow'));
    $this->assertFalse($this->set->equals($other));
  }
 
  #[@test]
  public function add() {
    $this->set->add(new String('green'));
    $this->assertFalse($this->set->isEmpty());
    $this->assertEquals(1, $this->set->size());
  }

  #[@test]
  public function addAll() {
    $array= array(new String('one'), new String('two'), new String('three'));
    $this->set->addAll($array);
    $this->assertFalse($this->set->isEmpty());
    $this->assertEquals(3, $this->set->size());
  }

  #[@test]
  public function addAllUniques() {
    $array= array(new String('one'), new String('one'), new String('two'));
    $this->set->addAll($array);
    $this->assertFalse($this->set->isEmpty());
    $this->assertEquals(2, $this->set->size());   // String{"one"} and String{"two"}
  }

  #[@test]
  public function addAllReturnsWhetherSetHasChanged() {
    $array= array(new String('caffeine'), new String('nicotine'));
    $this->assertTrue($this->set->addAll($array));
    $this->assertFalse($this->set->addAll($array));
    $this->assertFalse($this->set->addAll(array(new String('caffeine'))));
    $this->assertFalse($this->set->addAll([]));
  }

  #[@test]
  public function contains() {
    $this->set->add(new String('key'));
    $this->assertTrue($this->set->contains(new String('key')));
    $this->assertFalse($this->set->contains(new String('non-existant-key')));
  }

  #[@test]
  public function addSameValueTwice() {
    $color= new String('green');
    $this->assertTrue($this->set->add($color));
    $this->assertFalse($this->set->add($color));
  }

  #[@test]
  public function remove() {
    $this->set->add(new String('key'));
    $this->assertTrue($this->set->remove(new String('key')));
    $this->assertTrue($this->set->isEmpty());
  }

  #[@test]
  public function removeOnEmptySet() {
    $this->assertFalse($this->set->remove(new String('irrelevant-set-is-empty-anyway')));
  }

  #[@test]
  public function removeNonExistantObject() {
    $this->set->add(new String('key'));
    $this->assertFalse($this->set->remove(new String('non-existant-key')));
  }

  #[@test]
  public function clear() {
    $this->set->add(new String('key'));
    $this->set->clear();
    $this->assertTrue($this->set->isEmpty());
  }

  #[@test]
  public function toArray() {
    $color= new String('red');
    $this->set->add($color);
    $this->assertEquals(array($color), $this->set->toArray());
  }

  #[@test]
  public function toArrayOnEmptySet() {
    $this->assertEquals([], $this->set->toArray());
  }

  #[@test]
  public function iteration() {
    $this->set->add(new String('1'));
    $this->set->add(new String('2'));
    $this->set->add(new String('3'));
    
    foreach ($this->set as $i => $value) {
      $this->assertEquals(new String($i+ 1), $value);
    }
  }

  #[@test]
  public function stringRepresentation() {
    $this->set->add(new String('color'));
    $this->set->add(new String('price'));
    $this->assertEquals(
      "util.collections.HashSet[2] {\n  color,\n  price\n}",
      $this->set->toString()
    );
  }

  #[@test]
  public function stringRepresentationOfEmptySet() {
    $this->assertEquals(
      'util.collections.HashSet[0] { }',
      $this->set->toString()
    );
  }
}
