<?php namespace net\xp_framework\unittest\core;

use lang\{ClassLoader, Runnable};
use net\xp_framework\unittest\Name;
use net\xp_framework\unittest\core\generics\ListOf;
use unittest\{Test, TestCase, Values};

/** Tests the is() core functionality */
class IsTest extends TestCase {

  /** @return iterable */
  private function callables() {
    yield [function() { }];
    yield [function() { yield 'Test'; }];
    yield ['strlen'];
    yield ['xp::gc'];
    yield [['xp', 'gc']];
    yield [[new Name('test'), 'toString']];
  }

  /** @return iterable */
  private function iterables() {
    yield [[]];
    yield [[1, 2, 3]];
    yield [['key' => 'value']];
    yield [new \ArrayObject([])];
    yield [new \ArrayIterator([])];
  }

  /** @return iterable */
  private function objects() {
    yield [new Name('test')];
    yield [new \ArrayObject([])];
  }
  /** @return iterable */
  private function functions() {
    yield [function() { }];
    yield [function() { yield 'Test'; }];
  }


  #[Test]
  public function string_array() {
    $this->assertTrue(is('string[]', ['Hello']));
  }

  #[Test]
  public function var_array() {
    $this->assertFalse(is('string[]', ['Hello', 1, true]));
  }

  #[Test]
  public function int_array() {
    $this->assertTrue(is('int[]', [1, 2, 3]));
  }

  #[Test]
  public function mapIsNotAnInt_array() {
    $this->assertFalse(is('int[]', ['one' => 1, 'two' => 2]));
  }

  #[Test]
  public function intIsNotAnInt_array() {
    $this->assertFalse(is('int[]', 1));
  }

  #[Test]
  public function thisIsNotAnInt_array() {
    $this->assertFalse(is('int[]', $this));
  }

  #[Test]
  public function emptyArrayIsAnInt_array() {
    $this->assertTrue(is('int[]', []));
  }

  #[Test]
  public function object_array() {
    $this->assertTrue(is('net.xp_framework.unittest.Name[]', [new Name('test'), new Name('test'), new Name('test')]));
  }

  #[Test]
  public function objectArrayWithnull() {
    $this->assertFalse(is('net.xp_framework.unittest.Name[]', [new Name('test'), new Name('test'), null]));
  }

  #[Test]
  public function stringMap() {
    $this->assertTrue(is('[:string]', ['greet' => 'Hello', 'whom' => 'World']));
  }

  #[Test]
  public function intMap() {
    $this->assertTrue(is('[:int]', ['greet' => 1, 'whom' => 2]));
  }

  #[Test]
  public function intArrayIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', [1, 2, 3]));
  }

  #[Test]
  public function intIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', 1));
  }

  #[Test]
  public function thisIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', $this));
  }

  #[Test]
  public function emptyArrayIsAnIntMap() {
    $this->assertTrue(is('[:int]', []));
  }

  #[Test]
  public function stringPrimitive() {
    $this->assertTrue(is('string', 'Hello'));
  }

  #[Test]
  public function nullNotAStringPrimitive() {
    $this->assertFalse(is('string', null));
  }

  #[Test]
  public function boolPrimitive() {
    $this->assertTrue(is('bool', true));
  }

  #[Test]
  public function nullNotABoolPrimitive() {
    $this->assertFalse(is('bool', null));
  }

  #[Test]
  public function doublePrimitive() {
    $this->assertTrue(is('double', 0.0));
  }

  #[Test]
  public function nullNotADoublePrimitive() {
    $this->assertFalse(is('double', null));
  }

  #[Test]
  public function intPrimitive() {
    $this->assertTrue(is('int', 0));
  }

  #[Test]
  public function nullNotAnIntPrimitive() {
    $this->assertFalse(is('int', null));
  }

  #[Test]
  public function undefinedClassName() {
    $this->assertFalse(class_exists('Undefined_Class', false));
    $this->assertFalse(is('Undefined_Class', new class() { }));
  }

  #[Test]
  public function fullyQualifiedClassName() {
    $this->assertTrue(is('lang.Value', new Name('test')));
  }

  #[Test]
  public function interfaces() {
    ClassLoader::defineClass(
      'net.xp_framework.unittest.core.RunnableImpl', 
      null,
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
    $this->assertFalse(is('lang.Runnable', new class() { }));
  }

  #[Test]
  public function aStringVectorIsIsItself() {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.ListOf<string>', create('new net.xp_framework.unittest.core.generics.ListOf<string>')));
  }

  #[Test]
  public function aVectorIsNotAStringVector() {
    $this->assertFalse(is('net.xp_framework.unittest.core.generics.ListOf<string>', new ListOf()));
  }

  #[Test]
  public function aStringVectorIsNotAVector() {
    $this->assertFalse(is(
      'net.xp_framework.unittest.core.generics.ListOf',
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')
    ));
  }

  #[Test]
  public function anIntVectorIsNotAStringVector() {
    $this->assertFalse(is(
      'net.xp_framework.unittest.core.generics.ListOf<string>',
      create('new net.xp_framework.unittest.core.generics.ListOf<int>')
    ));
  }

  #[Test]
  public function aVectorOfIntVectorsIsItself() {
    $this->assertTrue(is(
      'net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }

  #[Test]
  public function aVectorOfIntVectorsIsNotAVectorOfStringVectors() {
    $this->assertFalse(is(
      'net.xp_framework.unittest.core.generics.ListOf<Vector<string>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }
 
  #[Test]
  public function anIntVectorIsNotAnUndefinedGeneric() {
    $this->assertFalse(is('Undefined_Class<string>', create('new net.xp_framework.unittest.core.generics.ListOf<int>')));
  }

  /** @return var[][] */
  private function genericDictionaries() {
    return [
      [create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>')],
      [create('new net.xp_framework.unittest.core.generics.Lookup<lang.Value, lang.Value>')],
      [create('new net.xp_framework.unittest.core.generics.Lookup<net.xp_framework.unittest.core.generics.ListOf<int>, lang.Value>')],
    ];
  }

  #[Test, Values('genericDictionaries')]
  public function wildcard_check_for_type_parameters($value) {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.Lookup<?, ?>', $value));
  }

  #[Test, Values('genericDictionaries')]
  public function wildcard_check_for_type_parameter_with_super_type($value) {
    $this->assertTrue(is('net.xp_framework.unittest.core.generics.IDictionary<?, ?>', $value));
  }

  #[Test]
  public function wildcard_check_for_single_type_parameter_generic() {
    $this->assertTrue(is(
      'net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<?>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }

  #[Test]
  public function wildcard_check_for_type_parameters_partial() {
    $this->assertTrue(is(
      'net.xp_framework.unittest.core.generics.Lookup<string, ?>',
      create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function wildcard_check_for_newinstance() {
    $this->assertTrue(is('util.Filter<?>', newinstance('util.Filter<string>', [], [
      'accept' => function($e) { return true; }
    ])));
  }

  #[Test]
  public function function_type() {
    $this->assertTrue(is('function(): var', function() { }));
  }

  #[Test]
  public function function_type_returning_array() {
    $this->assertTrue(is('function(): var[]', function() { }));
  }

  #[Test]
  public function braced_function_type() {
    $this->assertTrue(is('(function(): var)', function() { }));
  }

  #[Test]
  public function array_of_function_type() {
    $this->assertTrue(is('(function(): var)[]', [function() { }]));
  }

  #[Test, Values([1, 'Test'])]
  public function type_union($val) {
    $this->assertTrue(is('int|string', $val));
  }

  #[Test, Values([1, null])]
  public function nullable($val) {
    $this->assertTrue(is('?int', $val));
  }

  #[Test, Values('callables')]
  public function is_callable($val) {
    $this->assertTrue(is('callable', $val));
  }

  #[Test, Values([[[]], [[1, 2, 3]], [['key' => 'value']],])]
  public function is_array($val) {
    $this->assertTrue(is('array', $val));
  }

  #[Test, Values('iterables')]
  public function is_iterable($val) {
    $this->assertTrue(is('iterable', $val));
  }

  #[Test, Values('objects')]
  public function is_object($val) {
    $this->assertTrue(is('object', $val));
  }

  #[Test, Values('functions')]
  public function closures_are_objects($val) {
    $this->assertTrue(is('object', $val));
  }

  #[Test]
  public function type_intersection() {
    $this->assertTrue(is('Countable&Traversable', new \ArrayObject([])));
  }
}