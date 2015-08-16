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
use unittest\actions\RuntimeVersion;

/**
 * TestCase
 *
 * @see   xp://lang.Primitive
 * @deprecated Wrapper types will move to their own library
 */
class PrimitiveAndWrappersTest extends TestCase {

  #[@test]]
  public function boxString() {
    $this->assertEquals(new String('Hello'), Primitive::boxed('Hello'));
  }

  #[@test]]
  public function boxInteger() {
    $this->assertEquals(new Integer(1), Primitive::boxed(1));
  }

  #[@test]]
  public function boxDouble() {
    $this->assertEquals(new Double(1.0), Primitive::boxed(1.0));
  }

  #[@test]]
  public function boxBoolean() {
    $this->assertEquals(Boolean::$TRUE, Primitive::boxed(true), 'true');
    $this->assertEquals(Boolean::$FALSE, Primitive::boxed(false), 'false');
  }

  #[@test]]
  public function boxArray() {
    $this->assertEquals(new ArrayList(1, 2, 3), Primitive::boxed([1, 2, 3]));
  }

  #[@test]]
  public function boxObject() {
    $o= new \lang\Object();
    $this->assertEquals($o, Primitive::boxed($o));
  }

  #[@test]]
  public function boxNull() {
    $this->assertEquals(null, Primitive::boxed(null));
  }

  #[@test]]
  public function boxResource() {
    $fd= Streams::readableFd(new MemoryInputStream('test'));
    try {
      Primitive::boxed($fd);
    } catch (\lang\IllegalArgumentException $expected) {
      return;
    } finally {
      fclose($fd);    // Necessary, PHP will segfault otherwise
    }
    $this->fail('Expected exception not caught', null, 'lang.IllegalArgumentException');
  }

  #[@test]]
  public function unboxString() {
    $this->assertEquals('Hello', Primitive::unboxed(new String('Hello')));
  }

  #[@test]]
  public function unboxInteger() {
    $this->assertEquals(1, Primitive::unboxed(new Integer(1)));
  }

  #[@test]]
  public function unboxDouble() {
    $this->assertEquals(1.0, Primitive::unboxed(new Double(1.0)));
  }

  #[@test]]
  public function unboxBoolean() {
    $this->assertEquals(true, Primitive::unboxed(Boolean::$TRUE), 'true');
    $this->assertEquals(false, Primitive::unboxed(Boolean::$FALSE), 'false');
  }

  #[@test]]
  public function unboxArray() {
    $this->assertEquals([1, 2, 3], Primitive::unboxed(new ArrayList(1, 2, 3)));
  }

  #[@test, @expect('lang.IllegalArgumentException')]]
  public function unboxObject() {
    Primitive::unboxed(new \lang\Object());
  }

  #[@test]]
  public function unboxNull() {
    $this->assertEquals(null, Primitive::unboxed(null));
  }

  #[@test]
  public function unboxPrimitive() {
    $this->assertEquals(1, Primitive::unboxed(1));
  }

  #[@test, @values([
  #  ['4711', new Integer(4711)], ['32', new Short(32)],
  #  ['4711', new Double(4711.0)], ['4711', new Float(4711.0)],
  #  ['1', true], ['1', Boolean::$TRUE], ['', Boolean::$FALSE],
  #  ['Test', new String('Test')], ['', new String('')]
  #])]
  public function newInstance_of_string_with_wrappers($expected, $value) {
    $this->assertEquals($expected, Primitive::$STRING->newInstance($value));
  }

  #[@test, @values([
  #  [4711, new Integer(4711)], [32, new Short(32)],
  #  [4711, new Double(4711.0)], [4711, new Float(4711.0)],
  #  [1, Boolean::$TRUE], [0, Boolean::$FALSE],
  #  [4711, new String('4711')], [0, new String('')]
  #])]
  public function newInstance_of_int($expected, $value) {
    $this->assertEquals($expected, Primitive::$INT->newInstance($value));
  }

  #[@test, @values([
  #  [4711.0, new Integer(4711)], [32.0, new Short(32)],
  #  [47.11, new Double(47.11)], [47.11, new Float(47.11)],
  #  [1.0, Boolean::$TRUE], [0.0, Boolean::$FALSE],
  #  [47.11, new String('47.11')], [0.0, new String('')]
  #])]
  public function newInstance_of_double($expected, $value) {
    $this->assertEquals($expected, Primitive::$DOUBLE->newInstance($value));
  }

  #[@test, @values([
  #  [true, new Integer(4711)], [true, new Short(32)],
  #  [true, new Double(4711.0)], [true, new Float(4711.0)],
  #  [true, Boolean::$TRUE], [false, Boolean::$FALSE],
  #  [true, new String('Test')], [false, new String('')]
  #])]
  public function newInstance_of_bool($expected, $value) {
    $this->assertEquals($expected, Primitive::$BOOL->newInstance($value));
  }

  #[@test, @values([
  #  ['4711', new Integer(4711)], ['32', new Short(32)],
  #  ['4711', new Double(4711.0)], ['4711', new Float(4711.0)],
  #  ['1', Boolean::$TRUE], ['', Boolean::$FALSE],
  #  ['Test', new String('Test')], ['', new String('')]
  #])]
  public function cast_of_string($expected, $value) {
    $this->assertEquals($expected, Primitive::$STRING->cast($value));
  }

  #[@test, @values([
  #  [4711, new Integer(4711)], [32, new Short(32)],
  #  [4711, new Double(4711.0)], [4711, new Float(4711.0)],
  #  [1, Boolean::$TRUE], [0, Boolean::$FALSE],
  #  [4711, new String('4711')], [0, new String('')]
  #])]
  public function cast_of_int($expected, $value) {
    $this->assertEquals($expected, Primitive::$INT->cast($value));
  }

  #[@test, @values([
  #  [4711.0, new Integer(4711)], [32.0, new Short(32)],
  #  [47.11, new Double(47.11)], [47.11, new Float(47.11)],
  #  [1.0, Boolean::$TRUE], [0.0, Boolean::$FALSE],
  #  [47.11, new String('47.11')], [0.0, new String('')]
  #])]
  public function cast_of_double($expected, $value) {
    $this->assertEquals($expected, Primitive::$DOUBLE->cast($value));
  }

  #[@test, @values([
  #  [true, new Integer(4711)], [true, new Short(4711)],
  #  [true, new Double(4711.0)], [true, new Float(47.11)],
  #  [true, Boolean::$TRUE], [false, Boolean::$FALSE],
  #  [true, new String('Test')], [false, new String('')]
  #])]
  public function cast_of_bool($expected, $value) {
    $this->assertEquals($expected, Primitive::$BOOL->cast($value));
  }
}