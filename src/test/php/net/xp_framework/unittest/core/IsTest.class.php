<?php namespace net\xp_framework\unittest\core;

use lang\Object;
use lang\Runnable;
use lang\ClassLoader;
use net\xp_framework\unittest\core\generics\ListOf;

/**
 * Tests the is() core functionality
 *
 * @see      php://is_a
 */
class IsTest extends \unittest\TestCase {

  #[@test]
  public function string_array() {
    $this->assertTrue(is('string[]', ['Hello']));
  }

  #[@test]
  public function var_array() {
    $this->assertFalse(is('string[]', ['Hello', 1, true]));
  }

  #[@test]
  public function int_array() {
    $this->assertTrue(is('int[]', [1, 2, 3]));
  }

  #[@test]
  public function mapIsNotAnInt_array() {
    $this->assertFalse(is('int[]', ['one' => 1, 'two' => 2]));
  }

  #[@test]
  public function intIsNotAnInt_array() {
    $this->assertFalse(is('int[]', 1));
  }

  #[@test]
  public function thisIsNotAnInt_array() {
    $this->assertFalse(is('int[]', $this));
  }

  #[@test]
  public function emptyArrayIsAnInt_array() {
    $this->assertTrue(is('int[]', []));
  }

  #[@test]
  public function object_array() {
    $this->assertTrue(is('lang.Object[]', [new Object(), new Object(), new Object()]));
  }

  #[@test]
  public function objectArrayWithnull() {
    $this->assertFalse(is('lang.Object[]', [new Object(), new Object(), null]));
  }

  #[@test]
  public function stringMap() {
    $this->assertTrue(is('[:string]', ['greet' => 'Hello', 'whom' => 'World']));
  }

  #[@test]
  public function intMap() {
    $this->assertTrue(is('[:int]', ['greet' => 1, 'whom' => 2]));
  }

  #[@test]
  public function intArrayIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', [1, 2, 3]));
  }

  #[@test]
  public function intIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', 1));
  }

  #[@test]
  public function thisIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', $this));
  }

  #[@test]
  public function emptyArrayIsAnIntMap() {
    $this->assertTrue(is('[:int]', []));
  }

  #[@test]
  public function stringPrimitive() {
    $this->assertTrue(is('string', 'Hello'));
  }

  #[@test]
  public function nullNotAStringPrimitive() {
    $this->assertFalse(is('string', null));
  }

  #[@test]
  public function boolPrimitive() {
    $this->assertTrue(is('bool', true));
  }

  #[@test]
  public function nullNotABoolPrimitive() {
    $this->assertFalse(is('bool', null));
  }

  #[@test]
  public function doublePrimitive() {
    $this->assertTrue(is('double', 0.0));
  }

  #[@test]
  public function nullNotADoublePrimitive() {
    $this->assertFalse(is('double', null));
  }

  #[@test]
  public function intPrimitive() {
    $this->assertTrue(is('int', 0));
  }

  #[@test]
  public function nullNotAnIntPrimitive() {
    $this->assertFalse(is('int', null));
  }

  #[@test]
  public function undefinedClassName() {
    $this->assertFalse(class_exists('Undefined_Class', false));
    $this->assertFalse(is('Undefined_Class', new Object()));
  }

  #[@test]
  public function fullyQualifiedClassName() {
    $this->assertTrue(is('lang.Generic', new Object()));
  }

  #[@test]
  public function interfaces() {
    ClassLoader::defineClass(
      'net.xp_framework.unittest.core.RunnableImpl', 
      Object::class,
      [Runnable::class],
      ['run' => function() { }]
    );
    ClassLoader::defineClass(
      'net.xp_framework.unittest.core.RunnableImplEx', 
      'net.xp_framework.unittest.core.RunnableImpl',
      [],
      []
    );
    
    $this->assertTrue(is('lang.Runnable', new RunnableImpl()));
    $this->assertTrue(is('lang.Runnable', new RunnableImplEx()));
    $this->assertFalse(is('lang.Runnable', new Object()));
  }

  #[@test]
  public function aStringVectorIsIsItself() {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.ListOf<string>', create('new net.xp_framework.unittest.core.generics.ListOf<string>')));
  }

  #[@test]
  public function aVectorIsNotAStringVector() {
    $this->assertFalse(is('net.xp_framework.unittest.core.generics.ListOf<string>', new ListOf()));
  }

  #[@test]
  public function aStringVectorIsNotAVector() {
    $this->assertFalse(is(
      'net.xp_framework.unittest.core.generics.ListOf',
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')
    ));
  }

  #[@test]
  public function anIntVectorIsNotAStringVector() {
    $this->assertFalse(is(
      'net.xp_framework.unittest.core.generics.ListOf<string>',
      create('new net.xp_framework.unittest.core.generics.ListOf<int>')
    ));
  }

  #[@test]
  public function aVectorOfIntVectorsIsItself() {
    $this->assertTrue(is(
      'net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }

  #[@test]
  public function aVectorOfIntVectorsIsNotAVectorOfStringVectors() {
    $this->assertFalse(is(
      'net.xp_framework.unittest.core.generics.ListOf<Vector<string>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }
 
  #[@test]
  public function anIntVectorIsNotAnUndefinedGeneric() {
    $this->assertFalse(is('Undefined_Class<string>', create('new net.xp_framework.unittest.core.generics.ListOf<int>')));
  }

  /** @return var[][] */
  private function genericDictionaries() {
    return [
      [create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Generic>')],
      [create('new net.xp_framework.unittest.core.generics.Lookup<lang.Generic, lang.Generic>')],
      [create('new net.xp_framework.unittest.core.generics.Lookup<net.xp_framework.unittest.core.generics.ListOf<int>, lang.Generic>')],
    ];
  }

  #[@test, @values('genericDictionaries')]
  public function wildcard_check_for_type_parameters($value) {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.Lookup<?, ?>', $value));
  }

  #[@test, @values('genericDictionaries')]
  public function wildcard_check_for_type_parameter_with_super_type($value) {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.IDictionary<?, ?>', $value));
  }

  #[@test]
  public function wildcard_check_for_single_type_parameter_generic() {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<?>>', create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')));
  }

  #[@test]
  public function wildcard_check_for_type_parameters_partial() {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.Lookup<string, ?>', create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Generic>')));
  }

  #[@test]
  public function wildcard_check_for_newinstance() {
    $this->assertTrue(is('util.Filter<?>', newinstance('util.Filter<string>', [], [
      'accept' => function($e) { return true; }
    ])));
  }

  #[@test]
  public function function_type() {
    $this->assertTrue(is('function(): var', function() { }));
  }

  #[@test]
  public function function_type_returning_array() {
    $this->assertTrue(is('function(): var[]', function() { }));
  }

  #[@test]
  public function braced_function_type() {
    $this->assertTrue(is('(function(): var)', function() { }));
  }

  #[@test]
  public function array_of_function_type() {
    $this->assertTrue(is('(function(): var)[]', [function() { }]));
  }

  #[@test, @values([1, 'Test'])]
  public function type_union($val) {
    $this->assertTrue(is('int|string', $val));
  }

  #[@test, @values([
  #  [function() { }],
  #  [function() { yield 'Test'; }],
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'toString']]
  #])]
  public function callable($val) {
    $this->assertTrue(is('callable', $val));
  }

  #[@test, @values([
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']],
  #  [new \ArrayObject([])],
  #  [new \ArrayIterator([])]
  #])]
  public function iterable($val) {
    $this->assertTrue(is('iterable', $val));
  }

  #[@test, @values([
  #  [new Object()],
  #  [new \ArrayObject([])]
  #])]
  public function object($val) {
    $this->assertTrue(is('object', $val));
  }

  #[@test, @values([
  #  [function() { }],
  #  [function() { yield 'Test'; }]
  #])]
  public function closures_are_not_objects($val) {
    $this->assertFalse(is('object', $val));
  }
}
