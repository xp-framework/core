<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\Primitive;
use io\streams\Streams;
use io\streams\MemoryInputStream;

/**
 * TestCase
 *
 * @see   xp://lang.Primitive
 */
class PrimitiveTest extends TestCase {

  #[@test]
  public function stringPrimitive() {
    $this->assertEquals(Primitive::$STRING, Primitive::forName('string'));
  }

  #[@test]
  public function intPrimitive() {
    $this->assertEquals(Primitive::$INT, Primitive::forName('int'));
  }

  #[@test]
  public function doublePrimitive() {
    $this->assertEquals(Primitive::$DOUBLE, Primitive::forName('double'));
  }

  #[@test]
  public function boolPrimitive() {
    $this->assertEquals(Primitive::$BOOL, Primitive::forName('bool'));
  }

  #[@test]
  public function arrayPrimitive() {
    $this->assertEquals(Primitive::$ARRAY, Primitive::forName('array'));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function nonPrimitive() {
    Primitive::forName('lang.Object');
  }

  #[@test]
  public function boxString() {
    $this->assertEquals(new \lang\types\String('Hello'), Primitive::boxed('Hello'));
  }

  #[@test]
  public function boxInteger() {
    $this->assertEquals(new \lang\types\Integer(1), Primitive::boxed(1));
  }

  #[@test]
  public function boxDouble() {
    $this->assertEquals(new \lang\types\Double(1.0), Primitive::boxed(1.0));
  }

  #[@test]
  public function boxBoolean() {
    $this->assertEquals(new \lang\types\Boolean(true), Primitive::boxed(true), 'true');
    $this->assertEquals(new \lang\types\Boolean(false), Primitive::boxed(false), 'false');
  }

  #[@test]
  public function boxArray() {
    $this->assertEquals(new \lang\types\ArrayList(1, 2, 3), Primitive::boxed(array(1, 2, 3)));
  }

  #[@test]
  public function boxObject() {
    $o= new \lang\Object();
    $this->assertEquals($o, Primitive::boxed($o));
  }

  #[@test]
  public function boxNull() {
    $this->assertEquals(null, Primitive::boxed(null));
  }

  #[@test]
  public function boxResource() {
    $fd= Streams::readableFd(new MemoryInputStream('test'));
    try {
      Primitive::boxed($fd);
    } catch (\lang\IllegalArgumentException $expected) {
      // OK
    } ensure($expected); {
      fclose($fd);    // Necessary, PHP will segfault otherwise
      if ($expected) return;
    }
    $this->fail('Expected exception not caught', null, 'lang.IllegalArgumentException');
  }

  #[@test]
  public function unboxString() {
    $this->assertEquals('Hello', Primitive::unboxed(new \lang\types\String('Hello')));
  }

  #[@test]
  public function unboxInteger() {
    $this->assertEquals(1, Primitive::unboxed(new \lang\types\Integer(1)));
  }

  #[@test]
  public function unboxDouble() {
    $this->assertEquals(1.0, Primitive::unboxed(new \lang\types\Double(1.0)));
  }

  #[@test]
  public function unboxBoolean() {
    $this->assertEquals(true, Primitive::unboxed(new \lang\types\Boolean(true)), 'true');
    $this->assertEquals(false, Primitive::unboxed(new \lang\types\Boolean(false)), 'false');
  }

  #[@test]
  public function unboxArray() {
    $this->assertEquals(array(1, 2, 3), Primitive::unboxed(new \lang\types\ArrayList(1, 2, 3)));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function unboxObject() {
    Primitive::unboxed(new \lang\Object());
  }

  #[@test]
  public function unboxNull() {
    $this->assertEquals(null, Primitive::unboxed(null));
  }

  #[@test]
  public function unboxPrimitive() {
    $this->assertEquals(1, Primitive::unboxed(1));
  }

  /**
   * Returns instances of all types
   *
   * @param   var[] except
   * @return  var[]
   */
  public function instances($except) {
    $values= array(
      array($this), array(new \lang\types\String('Hello')), array(null),
      array(false), array(true),
      array(''), array('Hello'),
      array(0), array(-1),
      array(0.0), array(-1.5),
      array([]),
      array(array('one' => 'two'))
    );
    return array_filter($values, function($value) use ($except) {
      return !in_array($value[0], $except, true);
    });
  }

  #[@test, @values(array('', 'Hello'))]
  public function isAnInstanceOfStringPrimitive($value) {
    $this->assertTrue(Primitive::$STRING->isInstance($value));
  }
  
  #[@test, @values(source= 'instances', args= array(array('', 'Hello')))]
  public function notInstanceOfStringPrimitive($value) {
    $this->assertFalse(Primitive::$STRING->isInstance($value));
  }

  #[@test, @values(array(0, -1))]
  public function isAnInstanceOfIntegerPrimitive($value) {
    $this->assertTrue(Primitive::$INT->isInstance($value));
  }

  #[@test, @values(source= 'instances', args= array(array(0, -1)))]
  public function notInstanceOfIntegerPrimitive($value) {
    $this->assertFalse(Primitive::$INT->isInstance($value));
  }

  #[@test, @values(array(0.0, -1.5))]
  public function isAnInstanceOfDoublePrimitive($value) {
    $this->assertTrue(Primitive::$DOUBLE->isInstance($value));
  }

  #[@test, @values(source= 'instances', args= array(array(0.0, -1.5)))]
  public function notInstanceOfDoublePrimitive($value) {
    $this->assertFalse(Primitive::$DOUBLE->isInstance($value));
  }

  #[@test, @values(array(FALSE, TRUE))]
  public function isAnInstanceOfBooleanPrimitive($value) {
    $this->assertTrue(Primitive::$BOOL->isInstance($value));
  }

  #[@test, @values(source= 'instances', args= array(array(FALSE, TRUE)))]
  public function notInstanceOfBooleanPrimitive($value) {
    $this->assertFalse(Primitive::$BOOL->isInstance($value));
  }

  #[@test]
  public function stringIsAssignableFromString() {
    $this->assertTrue(Primitive::$STRING->isAssignableFrom('string'));
  }

  #[@test]
  public function stringIsAssignableFromStringType() {
    $this->assertTrue(Primitive::$STRING->isAssignableFrom(Primitive::$STRING));
  }

  #[@test]
  public function stringIsNotAssignableFromIntType() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom(Primitive::$INT));
  }

  #[@test]
  public function stringIsNotAssignableFromClassType() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom($this->getClass()));
  }

  #[@test]
  public function stringIsNotAssignableFromStringArray() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('string[]'));
  }

  #[@test]
  public function stringIsNotAssignableFromStringMap() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('[:string]'));
  }

  #[@test]
  public function stringIsNotAssignableFromVar() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('var'));
  }

  #[@test]
  public function stringIsNotAssignableFromVoid() {
    $this->assertFalse(Primitive::$STRING->isAssignableFrom('void'));
  }
}
