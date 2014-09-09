<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\Primitive;
use lang\types\String;
use lang\types\Double;
use lang\types\Integer;
use lang\types\Short;
use lang\types\Float;
use lang\types\Boolean;
use lang\types\ArrayList;
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

  #[@test, @expect('lang.IllegalArgumentException')]
  public function arrayPrimitive() {
    Primitive::forName('array');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function nonPrimitive() {
    Primitive::forName('lang.Object');
  }

  #[@test]
  public function boxString() {
    $this->assertEquals(new String('Hello'), Primitive::boxed('Hello'));
  }

  #[@test]
  public function boxInteger() {
    $this->assertEquals(new Integer(1), Primitive::boxed(1));
  }

  #[@test]
  public function boxDouble() {
    $this->assertEquals(new Double(1.0), Primitive::boxed(1.0));
  }

  #[@test]
  public function boxBoolean() {
    $this->assertEquals(Boolean::$TRUE, Primitive::boxed(true), 'true');
    $this->assertEquals(Boolean::$FALSE, Primitive::boxed(false), 'false');
  }

  #[@test]
  public function boxArray() {
    $this->assertEquals(new ArrayList(1, 2, 3), Primitive::boxed([1, 2, 3]));
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
    $this->assertEquals('Hello', Primitive::unboxed(new String('Hello')));
  }

  #[@test]
  public function unboxInteger() {
    $this->assertEquals(1, Primitive::unboxed(new Integer(1)));
  }

  #[@test]
  public function unboxDouble() {
    $this->assertEquals(1.0, Primitive::unboxed(new Double(1.0)));
  }

  #[@test]
  public function unboxBoolean() {
    $this->assertEquals(true, Primitive::unboxed(Boolean::$TRUE), 'true');
    $this->assertEquals(false, Primitive::unboxed(Boolean::$FALSE), 'false');
  }

  #[@test]
  public function unboxArray() {
    $this->assertEquals([1, 2, 3], Primitive::unboxed(new ArrayList(1, 2, 3)));
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
      array($this), array(new String('Hello')), array(null),
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

  #[@test, @values([
  #  ['', ''], ['Test', 'Test'],
  #  ['', null],
  #  ['0', 0], ['-1', -1], ['4711', new Integer(4711)], ['32', new Short(32)],
  #  ['0.5', 0.5], ['4711', new Double(4711.0)], ['4711', new Float(4711.0)],
  #  ['', false], ['1', true], ['1', Boolean::$TRUE], ['', Boolean::$FALSE],
  #  ['Test', new String('Test')], ['', new String('')]
  #])]
  public function newInstance_of_string($expected, $value) {
    $this->assertEquals($expected, Primitive::$STRING->newInstance($value));
  }

  #[@test, @values([
  #  [0, ''], [0, 'Test'], [123, '123'], [0xFF, '0xFF'], [0755, '0755'],
  #  [0, null],
  #  [0, 0], [-1, -1], [4711, new Integer(4711)], [32, new Short(32)],
  #  [0, 0.5], [4711, new Double(4711.0)], [4711, new Float(4711.0)],
  #  [0, false], [1, true], [1, Boolean::$TRUE], [0, Boolean::$FALSE],
  #  [4711, new String('4711')], [0, new String('')]
  #])]
  public function newInstance_of_int($expected, $value) {
    $this->assertEquals($expected, Primitive::$INT->newInstance($value));
  }

  #[@test, @values([
  #  [0.0, ''], [0.0, 'Test'], [123.0, '123'], [0.0, '0xFF'], [755.0, '0755'],
  #  [0.0, null],
  #  [0.0, 0], [-1.0, -1], [4711.0, new Integer(4711)], [32.0, new Short(32)],
  #  [0.5, 0.5], [47.11, new Double(47.11)], [47.11, new Float(47.11)],
  #  [0.0, false], [1.0, true], [1.0, Boolean::$TRUE], [0.0, Boolean::$FALSE],
  #  [47.11, new String('47.11')], [0.0, new String('')]
  #])]
  public function newInstance_of_double($expected, $value) {
    $this->assertEquals($expected, Primitive::$DOUBLE->newInstance($value));
  }

  #[@test, @values([
  #  [false, ''], [true, 'Test'],
  #  [false, null],
  #  [false, 0], [true, -1], [true, new Integer(4711)], [true, new Short(32)],
  #  [true, 0.5], [true, new Double(4711.0)], [true, new Float(4711.0)],
  #  [false, false], [true, true], [true, Boolean::$TRUE], [false, Boolean::$FALSE],
  #  [true, new String('Test')], [false, new String('')]
  #])]
  public function newInstance_of_bool($expected, $value) {
    $this->assertEquals($expected, Primitive::$BOOL->newInstance($value));
  }

  #[@test, @values([
  #  ['', ''], ['Test', 'Test'],
  #  [null, null],
  #  ['0', 0], ['-1', -1], ['4711', new Integer(4711)], ['32', new Short(32)],
  #  ['0.5', 0.5], ['4711', new Double(4711.0)], ['4711', new Float(4711.0)],
  #  ['', false], ['1', true], ['1', Boolean::$TRUE], ['', Boolean::$FALSE],
  #  ['Test', new String('Test')], ['', new String('')]
  #])]
  public function cast_of_string($expected, $value) {
    $this->assertEquals($expected, Primitive::$STRING->cast($value));
  }

  #[@test, @values([
  #  [0, ''], [0, 'Test'], [123, '123'], [0xFF, '0xFF'], [0755, '0755'],
  #  [null, null],
  #  [0, 0], [-1, -1], [4711, new Integer(4711)], [32, new Short(32)],
  #  [0, 0.5], [4711, new Double(4711.0)], [4711, new Float(4711.0)],
  #  [0, false], [1, true], [1, Boolean::$TRUE], [0, Boolean::$FALSE],
  #  [4711, new String('4711')], [0, new String('')]
  #])]
  public function cast_of_int($expected, $value) {
    $this->assertEquals($expected, Primitive::$INT->cast($value));
  }

  #[@test, @values([
  #  [0.0, ''], [0.0, 'Test'], [123.0, '123'], [0.0, '0xFF'], [755.0, '0755'],
  #  [null, null],
  #  [0.0, 0], [-1.0, -1], [4711.0, new Integer(4711)], [32.0, new Short(32)],
  #  [0.5, 0.5], [47.11, new Double(47.11)], [47.11, new Float(47.11)],
  #  [0.0, false], [1.0, true], [1.0, Boolean::$TRUE], [0.0, Boolean::$FALSE],
  #  [47.11, new String('47.11')], [0.0, new String('')]
  #])]
  public function cast_of_double($expected, $value) {
    $this->assertEquals($expected, Primitive::$DOUBLE->cast($value));
  }

  #[@test, @values([
  #  [false, ''], [true, 'Test'],
  #  [null, null],
  #  [false, 0], [true, -1], [true, new Integer(4711)], [true, new Short(4711)],
  #  [true, 0.5], [true, new Double(4711.0)], [true, new Float(47.11)],
  #  [false, false], [true, true], [true, Boolean::$TRUE], [false, Boolean::$FALSE],
  #  [true, new String('Test')], [false, new String('')]
  #])]
  public function cast_of_bool($expected, $value) {
    $this->assertEquals($expected, Primitive::$BOOL->cast($value));
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_create_instances_of_primitives_from_arrays($name) {
    Primitive::forName($name)->newInstance([1, 2, 3]);
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_create_instances_of_primitives_from_maps($name) {
    Primitive::forName($name)->newInstance(['one' => 'two']);
  }

  #[@test, @expect('lang.ClassCastException'), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_cast_arrays_to_primitives($name) {
    Primitive::forName($name)->cast([1, 2, 3]);
  }

  #[@test, @expect('lang.ClassCastException'), @values(['int', 'double', 'bool', 'string'])]
  public function cannot_cast_maps_to_primitives($name) {
    Primitive::forName($name)->cast(['one' => 'two']);
  }
}
