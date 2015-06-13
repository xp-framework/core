<?php namespace net\xp_framework\unittest\tests;
 
use lang\types\Integer;
use lang\types\ArrayList;
use net\xp_framework\unittest\Name;

/**
 * Test assertion methods
 */
class AssertionsTest extends \unittest\TestCase {

  #[@test]
  public function trueIsTrue() {
    $this->assertTrue(true);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function falseIsNotTrue() {
    $this->assertTrue(false);
  }

  #[@test]
  public function falseIsFalse() {
    $this->assertFalse(false);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function trueIsNotFalse() {
    $this->assertFalse(true);
  }

  #[@test]
  public function NullIsNull() {
    $this->assertNull(null);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function falseIsNotNull() {
    $this->assertNull(false);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function zeroIsNotNull() {
    $this->assertNull(0);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function emptyStringIsNotNull() {
    $this->assertNull('');
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function emptyArrayIsNotNull() {
    $this->assertNull([]);
  }

  #[@test]
  public function equalsMethodIsInvoked() {
    $instance= newinstance('lang.Object', [], '{
      public $equalsInvoked= 0;

      public function equals($other) {
        $this->equalsInvoked++;
        return $other instanceof self && $this->equalsInvoked == $other->equalsInvoked;
      }
    }');
   
    $this->assertEquals($instance, $instance);
    $this->assertNotEquals($instance, null);
    $this->assertEquals(2, $instance->equalsInvoked);
  }

  #[@test, @values([0, 1, -1, PHP_INT_MAX])]
  public function integersAreEqual($int) {
    $this->assertEquals($int, $int);
  }    

  #[@test, @values(['', 'Hello', 'äöüß'])]
  public function stringsAreEqual($str) {
    $this->assertEquals($str, $str);
  }    

  #[@test, @values([
  #  [[]],
  #  [[1, 2, 3]],
  #  [[[1], [], [-1, 4], [new Integer(2)]]]
  #])]
  public function arraysAreEqual($array) {
    $this->assertEquals($array, $array);
  }    

  #[@test, @values([
  #  [[]],
  #  [['foo' => 2]],
  #  [[['bar' => 'baz'], [], ['bool' => true, 'bar' => new Integer(6100)]]]
  #])]
  public function hashesAreEqual($hash) {
    $this->assertEquals($hash, $hash);
  }    

  #[@test]
  public function hashesOrderNotRelevant() {
    $hash= ['&' => '&amp;', '"' => '&quot;'];
    $this->assertEquals($hash, array_reverse($hash, true), \xp::stringOf($hash));
  }    

  #[@test, @values([1, 0, -1])]
  public function integerObjectsAreEqual($str) {
    $this->assertEquals(new Integer($str), new Integer($str));
  }

  #[@test, @values(['', 'Hello','äöüß'])]
  public function valuesAreEqual($str) {
    $this->assertEquals(new Name($str), new Name($str));
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function differentNotTypesAreNotEqual() {
    $this->assertEquals(false, null);
  }    

  #[@test, @values([-1, 1.0, null, false, true, '', [[1]], new Integer(1)])]
  public function integersAreNotEqual($cmp) {
    $this->assertNotEquals(1, $cmp);
  }    

  #[@test, @values([-1, 1.0, null, false, true, 1, [[1]], new Integer(1)])]
  public function stringsAreNotEqual($cmp) {
    $this->assertNotEquals('', $cmp);
  }

  #[@test, @values([-1, 1.0, null, false, true, 1, [[1]], new Integer(1)])]
  public function arraysAreNotEqual($cmp) {
    $this->assertNotEquals([], $cmp);
  }    

  #[@test, @expect('unittest.AssertionFailedError')]
  public function sameIntegersAreEqual() {
    $this->assertNotEquals(1, 1);
  }    

  #[@test]
  public function thisIsAnInstanceOfTestCase() {
    $this->assertInstanceOf('unittest.TestCase', $this);
  }

  #[@test]
  public function thisIsAnInstanceOfTestCaseClass() {
    $this->assertInstanceOf(\lang\XPClass::forName('unittest.TestCase'), $this);
  }    

  #[@test]
  public function thisIsAnInstanceOfObject() {
    $this->assertInstanceOf('lang.Object', $this);
  }    

  #[@test]
  public function objectIsAnInstanceOfObject() {
    $this->assertInstanceOf('lang.Object', new \lang\Object());
  }    

  #[@test, @expect('unittest.AssertionFailedError')]
  public function objectIsNotAnInstanceOfString() {
    $this->assertInstanceOf('lang.types.Integer', new \lang\Object());
  }    

  #[@test, @expect('unittest.AssertionFailedError')]
  public function zeroIsNotAnInstanceOfGeneric() {
    $this->assertInstanceOf('lang.Generic', 0);
  }    

  #[@test, @expect('unittest.AssertionFailedError')]
  public function nullIsNotAnInstanceOfGeneric() {
    $this->assertInstanceOf('lang.Generic', null);
  }    

  /** @deprecated */
  #[@test, @expect('unittest.AssertionFailedError')]
  public function xpNullIsNotAnInstanceOfGeneric() {
    $this->assertInstanceOf('lang.Generic', \xp::null());
  }    

  #[@test, @expect('unittest.AssertionFailedError')]
  public function thisIsNotAnInstanceOfString() {
    $this->assertInstanceOf('lang.types.Integer', $this);
  }    

  #[@test]
  public function thisIsAnInstanceOfGeneric() {
    $this->assertInstanceOf('lang.Generic', $this);
  }    

  #[@test]
  public function zeroIsInstanceOfInt() {
    $this->assertInstanceOf('int', 0);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function zeroPointZeroIsNotInstanceOfInt() {
    $this->assertInstanceOf('int', 0.0);
  }    

  #[@test]
  public function nullIsInstanceOfVar() {
    $this->assertInstanceOf(\lang\Type::$VAR, null);
  }    

  #[@test, @expect('unittest.AssertionFailedError')]
  public function nullIsNotInstanceOfVoidType() {
    $this->assertInstanceOf(\lang\Type::$VOID, null);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function nullIsNotInstanceOfVoid() {
    $this->assertInstanceOf('void', null);
  }

  #[@test]
  public function emptyArrayIsInstanceOfArray() {
    $this->assertInstanceOf('array', []);
  }

  #[@test]
  public function intArrayIsInstanceOfArray() {
    $this->assertInstanceOf('array', [1, 2, 3]);
  }

  #[@test]
  public function hashIsInstanceOfArray() {
    $this->assertInstanceOf('array', ['color' => 'green']);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function nullIsNotInstanceOfArray() {
    $this->assertInstanceOf('array', null);
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function arrayListIsNotInstanceOfArray() {
    $this->assertInstanceOf('array', new ArrayList(1, 2, 3));
  }

  #[@test, @expect('unittest.AssertionFailedError')]
  public function primitiveIsNotAnInstanceOfIntegerlass() {
    $this->assertInstanceOf('int', new Integer(1));
  }    
}
