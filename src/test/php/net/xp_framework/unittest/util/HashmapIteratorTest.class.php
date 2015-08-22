<?php namespace net\xp_framework\unittest\util;
 
use unittest\TestCase;
use util\Hashmap;
use util\HashmapIterator;
use util\Comparator;
use util\NoSuchElementException;

/**
 * Test HashmapIterator class
 *
 * @see  xp://util.HashmapIterator
 */
class HashmapIteratorTest extends TestCase {
  
  /**
   * Setup method. Creates the map
   *
   */
  public function setUp() {
    $this->map= new Hashmap(['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3']);
  }
      
  #[@test, @expect(NoSuchElementException::class)]
  public function nextOnEmpty() {
    (new HashmapIterator([]))->next();
  }

  #[@test]
  public function hasNextOnEmpty() {
    $this->assertFalse((new HashmapIterator([]))->hasNext());
  }

  #[@test]
  public function nextOnArray() {
    $this->assertEquals(
      'v1',
      (new HashmapIterator(['k1' => 'v1']))->next()
    );
  }

  #[@test]
  public function hasNextOnArray() {
    $this->assertTrue(
      (new HashmapIterator(['k1' => 'v1']))->hasNext()
    );
  }

  #[@test]
  public function nextFromHashmap() {
    $this->assertEquals(
      'v1',
      $this->map->iterator()->next()
    );
  }

  #[@test]
  public function nextFromHashmapKeys() {
    $this->assertEquals(
      'k1',
      $this->map->keyIterator()->next()
    );
  }

  #[@test, @expect(NoSuchElementException::class)]
  public function nextOnEnd() {
    $i= $this->map->iterator();
    $i->next();
    $i->next();
    $i->next();
    $i->next();
  }

  #[@test]
  public function hasNextOnEnd() {
    $i= $this->map->iterator();
    $i->next();
    $i->next();
    $i->next();
    $this->assertFalse($i->hasNext());
  }
}
