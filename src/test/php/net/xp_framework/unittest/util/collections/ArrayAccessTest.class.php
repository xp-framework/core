<?php namespace net\xp_framework\unittest\util\collections;

use util\collections\HashTable;
use util\collections\HashSet;
use util\collections\Vector;
use lang\IndexOutOfBoundsException;
use lang\IllegalArgumentException;

/**
 * TestCase
 *
 * @see   xp://util.collections.HashTable
 * @see   xp://util.collections.HashSet
 * @see   xp://util.collections.Vector
 */
class ArrayAccessTest extends \unittest\TestCase {

  #[@test]
  public function hashTableReadElement() {
    $c= new HashTable();
    $world= new Name('world');
    $c->put(new Name('hello'), $world);
    $this->assertEquals($world, $c[new Name('hello')]);
  }

  #[@test]
  public function hashTableReadNonExistantElement() {
    $c= new HashTable();
    $this->assertEquals(null, $c[new Name('hello')]);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function hashTableReadIllegalElement() {
    $c= create('new util.collections.HashTable<string, Object>()');
    $c[STDIN];
  }

  #[@test]
  public function hashTableWriteElement() {
    $c= new HashTable();
    $world= new Name('world');
    $c[new Name('hello')]= $world;
    $this->assertEquals($world, $c->get(new Name('hello')));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function hashTableWriteIllegalKey() {
    $c= create('new util.collections.HashTable<string, Object>()');
    $c[STDIN]= new Name('Hello');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function hashTableWriteIllegalValue() {
    $c= create('new util.collections.HashTable<string, Object>()');
    $c['hello']= 'scalar';
  }

  #[@test]
  public function hashTableTestElement() {
    $c= new HashTable();
    $c->put(new Name('hello'), new Name('world'));
    $this->assertTrue(isset($c[new Name('hello')]));
    $this->assertFalse(isset($c[new Name('world')]));
  }

  #[@test]
  public function hashTableRemoveElement() {
    $c= new HashTable();
    $c->put(new Name('hello'), new Name('world'));
    $this->assertTrue(isset($c[new Name('hello')]));
    unset($c[new Name('hello')]);
    $this->assertFalse(isset($c[new Name('hello')]));
  }

  #[@test]
  public function vectorReadElement() {
    $v= new Vector();
    $world= new Name('world');
    $v->add($world);
    $this->assertEquals($world, $v[0]);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function vectorReadNonExistantElement() {
    $v= new Vector();
    $v[0];
  }

  #[@test]
  public function vectorAddElement() {
    $v= new Vector();
    $world= new Name('world');
    $v[]= $world;
    $this->assertEquals($world, $v[0]);
  }
  
  #[@test]
  public function vectorWriteElement() {
    $v= new Vector([new Name('hello')]);
    $world= new Name('world');
    $v[0]= $world;
    $this->assertEquals($world, $v[0]);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function vectorWriteElementBeyondBoundsKey() {
    $v= new Vector();
    $v[0]= new Name('world');
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function vectorWriteElementNegativeKey() {
    $v= new Vector();
    $v[-1]= new Name('world');
  }

  #[@test]
  public function vectorTestElement() {
    $v= new Vector();
    $v[]= new Name('world');
    $this->assertTrue(isset($v[0]));
    $this->assertFalse(isset($v[1]));
    $this->assertFalse(isset($v[-1]));
  }

  #[@test]
  public function vectorRemoveElement() {
    $v= new Vector();
    $v[]= new Name('world');
    unset($v[0]);
    $this->assertFalse(isset($v[0]));
  }

  #[@test]
  public function vectorIsUsableInForeach() {
    $values= [new Name('hello'), new Name('world')];
    foreach (new Vector($values) as $i => $value) {
      $this->assertEquals($values[$i], $value);
    }
    $this->assertEquals(sizeof($values)- 1, $i);
  }

  #[@test]
  public function hashSetAddElement() {
    $s= new HashSet();
    $s[]= new Name('X');
    $this->assertTrue($s->contains(new Name('X')));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function hashSetWriteElement() {
    $s= new HashSet();
    $s[0]= new Name('X');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function hashSetReadElement() {
    $s= new HashSet();
    $s[]= new Name('X');
    $x= $s[0];
  }

  #[@test]
  public function hashSetTestElement() {
    $s= new HashSet();
    $this->assertFalse(isset($s[new Name('X')]));
    $s[]= new Name('X');
    $this->assertTrue(isset($s[new Name('X')]));
  }

  #[@test]
  public function hashSetRemoveElement() {
    $s= new HashSet();
    $s[]= new Name('X');
    unset($s[new Name('X')]);
    $this->assertFalse(isset($s[new Name('X')]));
  }

  #[@test]
  public function hashSetUsableInForeach() {
    $s= new HashSet();
    $s->addAll([new Name('0'), new Name('1'), new Name('2')]);
    foreach ($s as $i => $element) {
      $this->assertEquals(new Name($i), $element);
    }
  }
}
