<?php namespace net\xp_framework\unittest\util;
 
use unittest\TestCase;
use util\Hashmap;
use util\Comparator;
use lang\IllegalArgumentException;

/**
 * Test Hashmap class
 *
 * @see   xp://util.Hashmap
 */
class HashmapTest extends TestCase {
  public $map= null;
  
  /**
   * Setup method. Creates the map member
   */
  public function setUp() {
    $this->map= new Hashmap();
  }
      
  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue($this->map->isEmpty());
  }

  #[@test]
  public function equalsClone() {
    $this->map->put('color', 'green');
    $this->assertTrue($this->map->equals(clone($this->map)));
  }
 
  #[@test]
  public function equalsOtherMapWithSameContents() {
    $other= new Hashmap();
    $this->map->put('color', 'green');
    $other->put('color', 'green');
    $this->assertTrue($this->map->equals($other));
  }

  #[@test]
  public function doesNotEqualMapWithDifferentContents() {
    $other= new Hashmap();
    $this->map->put('color', 'green');
    $other->put('color', 'pink');
    $this->assertFalse($this->map->equals($other));
  }
 
  #[@test]
  public function put() {
    $this->map->put('color', 'green');
    $this->assertFalse($this->map->isEmpty());
    $this->assertEquals(1, $this->map->size());
  }

  #[@test]
  public function get() {
    $this->map->put('key', 'value');
    $this->assertEquals('value', $this->map->get('key'));
  }

  #[@test]
  public function remove() {
    $this->map->put('key', 'value');
    $this->map->remove('key');
    $this->assertTrue($this->map->isEmpty());
  }

  #[@test]
  public function getReturnsNullOnEmptyList() {
    $this->assertTrue($this->map->isEmpty());
    $this->assertNull($this->map->get('key'));
  }

  #[@test]
  public function containsKey() {
    $this->map->put('key', 'value');
    $this->assertTrue($this->map->containsKey('key'));
    $this->assertFalse($this->map->containsKey('non-existant-key'));
  }
  
  /**
   * Helper method for merge* test methods
   *
   * @param   bool recursive default FALSE Merge hashmaps recursively
   * @param   [:var] toMerge
   * @param   [:var] expect
   */
  protected function testMerge($recursive, $toMerge, $expect) {
    $this->map->put('color', 'red');
    $this->map->put('count', 5);

    $this->map->merge($toMerge, $recursive);
    $this->assertEquals($expect, $this->map->toArray());
  }

  #[@test]
  public function merge() {
    $this->testMerge(
      false,
      ['color' => 'green', 'key' => 'value'],
      ['color' => 'red', 'key' => 'value', 'count' => 5]
    );
  }

  #[@test]
  public function mergeRecursive() {
    $this->testMerge(
      true,
      ['color' => 'green', 'key' => 'value'],
      ['color' => ['green', 'red'], 'key' => 'value', 'count' => 5]
    );
  }
  
  #[@test, @expect(IllegalArgumentException::class)]    
  public function mergeWithIllegalArgument() {
    $this->map->merge(new \lang\Object());
  }

  #[@test]
  public function swap() {
    $this->map->put('color', 'purple');
    $this->map->put('price', 15);

    $this->assertTrue($this->map->swap('color', 'price'));
    $this->assertEquals(
      ['color' => 15, 'price' => 'purple'],
      $this->map->toArray()
    );
  }

  #[@test]
  public function swapNonExistantKeys() {
    $this->map->put('color', 'purple');
    $this->map->put('price', 15);

    $this->assertFalse($this->map->swap('color', 'non-existant-key'));
    $this->assertFalse($this->map->swap('non-existant-key', 'color'));
  }

  #[@test]
  public function flip() {
    $this->map->put('color', 'purple');
    $this->map->put('price', 15);

    $this->assertTrue($this->map->flip());
    $this->assertEquals(
      ['purple' => 'color', 15 => 'price'],
      $this->map->toArray()
    );
  }

  #[@test]
  public function clear() {
    $this->map->put('key', 'value');
    $this->map->clear();
    $this->assertTrue($this->map->isEmpty());
  }

  #[@test]
  public function containsValue() {
    $this->map->put('key', 'value');
    $this->assertTrue($this->map->containsValue($v= 'value'));
    $this->assertFalse($this->map->containsValue($v= 'non-existant-value'));
  }

  #[@test]
  public function keys() {
    $this->map->put('one', 1);
    $this->map->put('two', 2);
    $this->assertEquals(['one', 'two'], $this->map->keys());
  }

  #[@test]
  public function values() {
    $this->map->put('one', 1);
    $this->map->put('two', 2);
    $this->assertEquals([1, 2], $this->map->values());
  }

  #[@test]
  public function filter() {
    $this->map->put('one', 1);
    $this->map->put('two', 2);
    $this->map->put('three', 3);
    $this->map->put('four', 4);
    $this->map->filter(function($v) { return 1 === $v % 2; });
    $this->assertEquals(['one' => 1, 'three' => 3], $this->map->toArray());
  }

  #[@test]
  public function sort() {
    $this->map->put('two', 2);
    $this->map->put('one', 1);
    $this->map->sort(SORT_NUMERIC);

    $this->assertEquals(
      ['one' => 1, 'two' => 2],
      $this->map->toArray()
    );
  }

  #[@test]
  public function rsort() {
    $this->map->put('one', 1);
    $this->map->put('two', 2);
    $this->map->rsort(SORT_NUMERIC);

    $this->assertEquals(
      ['two' => 2, 'one' => 1],
      $this->map->toArray()
    );
  }

  #[@test]
  public function usort() {
    $this->map->put('one', 'One');
    $this->map->put('two', 'two');
    $this->map->put('eins', 'one');

    $this->map->usort(newinstance(Comparator::class, [], [
      'compare' => function($a, $b) { 
        return strcasecmp($a, $b); 
      }
    ]));
    $this->assertEquals(
      ['one' => 'One', 'eins' => 'one', 'two' => 'two'],
      $this->map->toArray()
    );
  }

  #[@test]
  public function valueIteration() {
    $this->map->put('one', 1);
    $this->map->put('two', 2);
    $this->map->put('three', 3);
    for ($it= $this->map->iterator(), $i= 1; $it->hasNext(); ) {
      $this->assertEquals($i, $it->next());
      $i++;
    }
    $this->assertEquals(4, $i);
  }    

  #[@test]
  public function keyIteration() {
    $this->map->put(1, 'one');
    $this->map->put(2, 'two');
    $this->map->put(3, 'three');
    for ($it= $this->map->keyIterator(), $i= 1; $it->hasNext(); ) {
      $this->assertEquals($i, $it->next());
      $i++;
    }
    $this->assertEquals(4, $i);
  }    

  #[@test]
  public function containsKey_should_return_true_even_for_null_values() {
    $map= new Hashmap();
    $map->put("myKey", null);

    $this->assertTrue($map->containsKey("myKey"));
  }

  #[@test]
  public function overloads_reading_nonexistant() {
    $map= new Hashmap();
    $this->assertNull($map['key']);
  }

  #[@test]
  public function overloads_reading_existing() {
    $map= new Hashmap();
    $map->put('key', 'value');
    $this->assertEquals('value', $map['key']);
  }

  #[@test]
  public function overloads_writing() {
    $map= new Hashmap();
    $map['key']= 'value';
    $this->assertEquals('value', $map->get('key'));
  }

  #[@test]
  public function overloads_isset_nonexistant() {
    $map= new Hashmap();
    $this->assertFalse(isset($map['key']));
  }

  #[@test]
  public function overloads_isset_existing() {
    $map= new Hashmap();
    $map->put('key', 'value');
    $this->assertTrue(isset($map['key']));
  }

  #[@test]
  public function overloads_unset() {
    $map= new Hashmap();
    $map->put('key', 'value');
    unset($map['key']);
    $this->assertFalse(isset($map['key']));
  }
}
