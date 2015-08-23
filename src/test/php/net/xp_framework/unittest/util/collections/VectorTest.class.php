<?php namespace net\xp_framework\unittest\util\collections;

use lang\Object;
use lang\IllegalArgumentException;
use lang\IndexOutOfBoundsException;
use lang\types\ArrayList;
use util\collections\Vector;

/**
 * TestCase for vector class
 *
 * @see  xp://util.collections.Vector
 */
class VectorTest extends \unittest\TestCase {

  #[@test]
  public function initiallyEmpty() {
    $this->assertTrue((new Vector())->isEmpty());
  }

  #[@test]
  public function sizeOfEmptyVector() {
    $this->assertEquals(0, (new Vector())->size());
  }
  
  #[@test]
  public function nonEmptyVector() {
    $v= new Vector([new Object()]);
    $this->assertEquals(1, $v->size());
    $this->assertFalse($v->isEmpty());
  }

  #[@test]
  public function adding() {
    $v= new Vector();
    $v->add(new Object());
    $this->assertEquals(1, $v->size());
  }

  #[@test]
  public function addAllArray() {
    $v= new Vector();
    $this->assertTrue($v->addAll([new Object(), new Object()]));
    $this->assertEquals(2, $v->size());
  }

  #[@test]
  public function addAllVector() {
    $v1= new Vector();
    $v2= new Vector();
    $v2->add(new Object());
    $v2->add(new Object());
    $this->assertTrue($v1->addAll($v2));
    $this->assertEquals(2, $v1->size());
  }

  #[@test]
  public function addAllArrayList() {
    $v= new Vector();
    $this->assertTrue($v->addAll(new ArrayList(new Object(), new Object())));
    $this->assertEquals(2, $v->size());
  }

  #[@test]
  public function addAllEmptyArray() {
    $this->assertFalse((new Vector())->addAll([]));
  }

  #[@test]
  public function addAllEmptyVector() {
    $this->assertFalse((new Vector())->addAll(new Vector()));
  }

  #[@test]
  public function addAllEmptyArrayList() {
    $this->assertFalse((new Vector())->addAll(new ArrayList()));
  }

  #[@test]
  public function unchangedAfterNullInAddAll() {
    $v= create('new util.collections.Vector<Object>()');
    try {
      $v->addAll([new Object(), null]);
      $this->fail('addAll() did not throw an exception', null, 'lang.IllegalArgumentException');
    } catch (IllegalArgumentException $expected) {
    }
    $this->assertTrue($v->isEmpty());
  }

  #[@test]
  public function unchangedAfterIntInAddAll() {
    $v= create('new util.collections.Vector<string>()');
    try {
      $v->addAll(['hello', 5]);
      $this->fail('addAll() did not throw an exception', null, 'lang.IllegalArgumentException');
    } catch (IllegalArgumentException $expected) {
    }
    $this->assertTrue($v->isEmpty());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function addingNull() {
    create('new util.collections.Vector<Object>()')->add(null);
  }

  #[@test]
  public function replacing() {
    $v= new Vector();
    $o= new Name('one');
    $v->add($o);
    $r= $v->set(0, new Name('two'));
    $this->assertEquals(1, $v->size());
    $this->assertEquals($o, $r);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function replacingWithNull() {
    create('new util.collections.Vector<Object>', [new Object()])->set(0, null);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function settingPastEnd() {
    (new Vector())->set(0, new Object());
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function settingNegative() {
    (new Vector())->set(-1, new Object());
  }

  #[@test]
  public function reading() {
    $v= new Vector();
    $o= new Name('one');
    $v->add($o);
    $r= $v->get(0);
    $this->assertEquals($o, $r);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function readingPastEnd() {
    (new Vector())->get(0);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function readingNegative() {
    (new Vector())->get(-1);
  }

  #[@test]
  public function removing() {
    $v= new Vector();
    $o= new Name('one');
    $v->add($o);
    $r= $v->remove(0);
    $this->assertEquals(0, $v->size());
    $this->assertEquals($o, $r);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function removingPastEnd() {
    (new Vector())->get(0);
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function removingNegative() {
    (new Vector())->get(-1);
  }

  #[@test]
  public function clearing() {
    $v= new Vector([new Name('Goodbye cruel world')]);
    $this->assertFalse($v->isEmpty());
    $v->clear();
    $this->assertTrue($v->isEmpty());
  }

  #[@test]
  public function elementsOfEmptyVector() {
    $this->assertEquals([], (new Vector())->elements());
  }

  #[@test]
  public function elementsOf() {
    $el= [new Name('a'), new Object()];
    $this->assertEquals($el, (new Vector($el))->elements());
  }

  #[@test]
  public function addedNameIsContained() {
    $v= new Vector();
    $o= new Name('one');
    $v->add($o);
    $this->assertTrue($v->contains($o));
  }

  #[@test]
  public function emptyVectorDoesNotContainName() {
    $this->assertFalse((new Vector())->contains(new Object()));
  }

  #[@test]
  public function indexOfOnEmptyVector() {
    $this->assertFalse((new Vector())->indexOf(new Object()));
  }

  #[@test]
  public function indexOf() {
    $a= new Name('A');
    $this->assertEquals(0, (new Vector([$a]))->indexOf($a));
  }

  #[@test]
  public function indexOfElementContainedTwice() {
    $a= new Name('A');
    $this->assertEquals(0, (new Vector([$a, new Object(), $a]))->indexOf($a));
  }

  #[@test]
  public function lastIndexOfOnEmptyVector() {
    $this->assertFalse((new Vector())->lastIndexOf(new Object()));
  }

  #[@test]
  public function lastIndexOf() {
    $a= new Name('A');
    $this->assertEquals(0, (new Vector([$a]))->lastIndexOf($a));
  }

  #[@test]
  public function lastIndexOfElementContainedTwice() {
    $a= new Name('A');
    $this->assertEquals(2, (new Vector([$a, new Object(), $a]))->lastIndexOf($a));
  }

  #[@test]
  public function stringOfEmptyVector() {
    $this->assertEquals(
      "util.collections.Vector[0]@{\n}",
      (new Vector())->toString()
    );
  }

  #[@test]
  public function stringOf() {
    $this->assertEquals(
      "util.collections.Vector[2]@{\n  0: One\n  1: Two\n}",
      (new Vector([new Name('One'), new Name('Two')]))->toString()
    );
  }

  #[@test]
  public function iteration() {
    $v= new Vector();
    for ($i= 0; $i < 5; $i++) {
      $v->add(new Name('#'.$i));
    }
    
    $i= 0;
    foreach ($v as $offset => $string) {
      $this->assertEquals($offset, $i);
      $this->assertEquals(new Name('#'.$i), $string);
      $i++;
    }
  }

  #[@test]
  public function twoEmptyVectorsAreEqual() {
    $this->assertTrue((new Vector())->equals(new Vector()));
  }

  #[@test]
  public function sameVectorsAreEqual() {
    $a= new Vector([new Name('One'), new Name('Two')]);
    $this->assertTrue($a->equals($a));
  }

  #[@test]
  public function vectorsWithSameContentsAreEqual() {
    $a= new Vector([new Name('One'), new Name('Two')]);
    $b= new Vector([new Name('One'), new Name('Two')]);
    $this->assertTrue($a->equals($b));
  }

  #[@test]
  public function aVectorIsNotEqualToNull() {
    $this->assertFalse((new Vector())->equals(null));
  }

  #[@test]
  public function twoVectorsOfDifferentSizeAreNotEqual() {
    $this->assertFalse((new Vector([new Object()]))->equals(new Vector()));
  }

  #[@test]
  public function orderMattersForEquality() {
    $a= [new Name('a'), new Name('b')];
    $b= [new Name('b'), new Name('a')];
    $this->assertFalse((new Vector($a))->equals(new Vector($b)));
  }

  #[@test]
  public function addFunction() {
    $f= function() { return 'test'; };
    $this->assertEquals($f, (new Vector([$f]))[0]);
  }

  #[@test]
  public function addFunctions() {
    $f= [function() { return 'one'; }, function() { return 'two'; }];
    $this->assertEquals($f, (new Vector($f))->elements());
  }
}
