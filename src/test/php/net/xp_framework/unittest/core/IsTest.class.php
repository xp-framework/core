<?php namespace net\xp_framework\unittest\core;

use lang\{ClassLoader, Runnable};
use net\xp_framework\unittest\Name;
use net\xp_framework\unittest\core\generics\ListOf;
use unittest\Assert;
use unittest\{Test, TestCase, Values};

/** Tests the is() core functionality */
class IsTest {

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
    Assert::true(is('string[]', ['Hello']));
  }

  #[Test]
  public function var_array() {
    Assert::false(is('string[]', ['Hello', 1, true]));
  }

  #[Test]
  public function int_array() {
    Assert::true(is('int[]', [1, 2, 3]));
  }

  #[Test]
  public function mapIsNotAnInt_array() {
    Assert::false(is('int[]', ['one' => 1, 'two' => 2]));
  }

  #[Test]
  public function intIsNotAnInt_array() {
    Assert::false(is('int[]', 1));
  }

  #[Test]
  public function thisIsNotAnInt_array() {
    Assert::false(is('int[]', $this));
  }

  #[Test]
  public function emptyArrayIsAnInt_array() {
    Assert::true(is('int[]', []));
  }

  #[Test]
  public function object_array() {
    Assert::true(is('net.xp_framework.unittest.Name[]', [new Name('test'), new Name('test'), new Name('test')]));
  }

  #[Test]
  public function objectArrayWithnull() {
    Assert::false(is('net.xp_framework.unittest.Name[]', [new Name('test'), new Name('test'), null]));
  }

  #[Test]
  public function stringMap() {
    Assert::true(is('[:string]', ['greet' => 'Hello', 'whom' => 'World']));
  }

  #[Test]
  public function intMap() {
    Assert::true(is('[:int]', ['greet' => 1, 'whom' => 2]));
  }

  #[Test]
  public function intArrayIsNotAnIntMap() {
    Assert::false(is('[:int]', [1, 2, 3]));
  }

  #[Test]
  public function intIsNotAnIntMap() {
    Assert::false(is('[:int]', 1));
  }

  #[Test]
  public function thisIsNotAnIntMap() {
    Assert::false(is('[:int]', $this));
  }

  #[Test]
  public function emptyArrayIsAnIntMap() {
    Assert::true(is('[:int]', []));
  }

  #[Test]
  public function stringPrimitive() {
    Assert::true(is('string', 'Hello'));
  }

  #[Test]
  public function nullNotAStringPrimitive() {
    Assert::false(is('string', null));
  }

  #[Test]
  public function boolPrimitive() {
    Assert::true(is('bool', true));
  }

  #[Test]
  public function nullNotABoolPrimitive() {
    Assert::false(is('bool', null));
  }

  #[Test]
  public function doublePrimitive() {
    Assert::true(is('double', 0.0));
  }

  #[Test]
  public function nullNotADoublePrimitive() {
    Assert::false(is('double', null));
  }

  #[Test]
  public function intPrimitive() {
    Assert::true(is('int', 0));
  }

  #[Test]
  public function nullNotAnIntPrimitive() {
    Assert::false(is('int', null));
  }

  #[Test]
  public function undefinedClassName() {
    Assert::false(class_exists('Undefined_Class', false));
    Assert::false(is('Undefined_Class', new class() { }));
  }

  #[Test]
  public function fullyQualifiedClassName() {
    Assert::true(is('lang.Value', new Name('test')));
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
    
    Assert::true(is('lang.Runnable', new RunnableImpl()));
    Assert::true(is('lang.Runnable', new RunnableImplEx()));
    Assert::false(is('lang.Runnable', new class() { }));
  }

  #[Test]
  public function aStringVectorIsIsItself() {
    Assert::true(is('net.xp_framework.unittest.core.generics.ListOf<string>', create('new net.xp_framework.unittest.core.generics.ListOf<string>')));
  }

  #[Test]
  public function aVectorIsNotAStringVector() {
    Assert::false(is('net.xp_framework.unittest.core.generics.ListOf<string>', new ListOf()));
  }

  #[Test]
  public function aStringVectorIsNotAVector() {
    Assert::false(is(
      'net.xp_framework.unittest.core.generics.ListOf',
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')
    ));
  }

  #[Test]
  public function anIntVectorIsNotAStringVector() {
    Assert::false(is(
      'net.xp_framework.unittest.core.generics.ListOf<string>',
      create('new net.xp_framework.unittest.core.generics.ListOf<int>')
    ));
  }

  #[Test]
  public function aVectorOfIntVectorsIsItself() {
    Assert::true(is(
      'net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }

  #[Test]
  public function aVectorOfIntVectorsIsNotAVectorOfStringVectors() {
    Assert::false(is(
      'net.xp_framework.unittest.core.generics.ListOf<Vector<string>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }
 
  #[Test]
  public function anIntVectorIsNotAnUndefinedGeneric() {
    Assert::false(is('Undefined_Class<string>', create('new net.xp_framework.unittest.core.generics.ListOf<int>')));
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
    Assert::true(is('net.xp_framework.unittest.core.generics.Lookup<?, ?>', $value));
  }

  #[Test, Values('genericDictionaries')]
  public function wildcard_check_for_type_parameter_with_super_type($value) {
    Assert::true(is('net.xp_framework.unittest.core.generics.IDictionary<?, ?>', $value));
  }

  #[Test]
  public function wildcard_check_for_single_type_parameter_generic() {
    Assert::true(is(
      'net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<?>>',
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.generics.ListOf<int>>')
    ));
  }

  #[Test]
  public function wildcard_check_for_type_parameters_partial() {
    Assert::true(is(
      'net.xp_framework.unittest.core.generics.Lookup<string, ?>',
      create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function wildcard_check_for_newinstance() {
    Assert::true(is('util.Filter<?>', newinstance('util.Filter<string>', [], [
      'accept' => function($e) { return true; }
    ])));
  }

  #[Test]
  public function function_type() {
    Assert::true(is('function(): var', function() { }));
  }

  #[Test]
  public function function_type_returning_array() {
    Assert::true(is('function(): var[]', function() { }));
  }

  #[Test]
  public function braced_function_type() {
    Assert::true(is('(function(): var)', function() { }));
  }

  #[Test]
  public function array_of_function_type() {
    Assert::true(is('(function(): var)[]', [function() { }]));
  }

  #[Test, Values([1, 'Test'])]
  public function type_union($val) {
    Assert::true(is('int|string', $val));
  }

  #[Test, Values([1, null])]
  public function nullable($val) {
    Assert::true(is('?int', $val));
  }

  #[Test, Values('callables')]
  public function is_callable($val) {
    Assert::true(is('callable', $val));
  }

  #[Test, Values([[[]], [[1, 2, 3]], [['key' => 'value']],])]
  public function is_array($val) {
    Assert::true(is('array', $val));
  }

  #[Test, Values('iterables')]
  public function is_iterable($val) {
    Assert::true(is('iterable', $val));
  }

  #[Test, Values('objects')]
  public function is_object($val) {
    Assert::true(is('object', $val));
  }

  #[Test, Values('functions')]
  public function closures_are_objects($val) {
    Assert::true(is('object', $val));
  }

  #[Test]
  public function type_intersection() {
    Assert::true(is('Countable&Traversable', new \ArrayObject([])));
  }
}