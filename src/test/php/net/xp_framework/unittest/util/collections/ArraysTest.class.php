<?php namespace net\xp_framework\unittest\util\collections;

use unittest\TestCase;
use lang\types\Integer;
use lang\types\Float;
use lang\types\ArrayList;
use util\collections\Arrays;
use util\collections\IList;
use util\Comparator;

/**
 * TestCase
 *
 * @see   xp://util.collections.Arrays
 */
class ArraysTest extends TestCase {

  #[@test]
  public function asList() {
    $list= Arrays::asList(new ArrayList(new Integer(1), new Integer(2), new Integer(3)));
    $this->assertInstanceOf(IList::class, $list);
    $this->assertEquals(3, $list->size());
    $this->assertEquals(new Integer(1), $list->get(0));
    $this->assertEquals(new Integer(2), $list->get(1));
    $this->assertEquals(new Integer(3), $list->get(2));
  }

  #[@test]
  public function asListWithPrimitives() {
    $list= Arrays::asList(new ArrayList('one', 'two', 'three'));
    $this->assertInstanceOf(IList::class, $list);
    $this->assertEquals(3, $list->size());
    $this->assertEquals('one', $list->get(0));
    $this->assertEquals('two', $list->get(1));
    $this->assertEquals('three', $list->get(2));
  }

  #[@test]
  public function empty_array() {
    $this->assertEquals(ArrayList::newInstance(0), Arrays::$EMPTY);
  }

  #[@test]
  public function sort() {
    $a= new ArrayList(1, 4, 3, 2);
    Arrays::sort($a);
    $this->assertEquals(new ArrayList(1, 2, 3, 4), $a);
  }

  #[@test]
  public function sortWithComparator() {
    $a= new ArrayList(new Integer(2), new Integer(4), new Integer(3));
    Arrays::sort($a, newinstance(Comparator::class, [], [
      'compare' => function($a, $b) {
        return $a->value - $b->value;
      }
    ]));
    $this->assertEquals(new ArrayList(new Integer(2), new Integer(3), new Integer(4)), $a);
  }

  #[@test]
  public function sorted() {
    $a= new ArrayList(1, 4, 3, 2);
    $this->assertEquals(new ArrayList(1, 2, 3, 4), Arrays::sorted($a));
    $this->assertEquals(new ArrayList(1, 4, 3, 2), $a);
  }

  #[@test]
  public function containsWithPrimitives() {
    $a= new ArrayList(1, 4, 3, 2);
    $this->assertTrue(Arrays::contains($a, 1));
    $this->assertFalse(Arrays::contains($a, 5));
    $this->assertFalse(Arrays::contains($a, '1'));
  }

  #[@test]
  public function containsWithGenerics() {
    $a= new ArrayList(new Integer(1), new Integer(2), new Integer(3));
    $this->assertTrue(Arrays::contains($a, new Integer(1)));
    $this->assertFalse(Arrays::contains($a, new Integer(5)));
    $this->assertFalse(Arrays::contains($a, new Float(1.0)));
  }
}
