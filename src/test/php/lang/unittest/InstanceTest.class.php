<?php namespace lang\unittest;

use lang\unittest\ListOf;
use lang\{ClassLoader, Runnable};
use test\{Assert, Test, Values};

class InstanceTest {

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
    Assert::true(instance('string[]', ['Hello']));
  }

  #[Test]
  public function var_array() {
    Assert::false(instance('string[]', ['Hello', 1, true]));
  }

  #[Test]
  public function int_array() {
    Assert::true(instance('int[]', [1, 2, 3]));
  }

  #[Test]
  public function mapIsNotAnInt_array() {
    Assert::false(instance('int[]', ['one' => 1, 'two' => 2]));
  }

  #[Test]
  public function intIsNotAnInt_array() {
    Assert::false(instance('int[]', 1));
  }

  #[Test]
  public function thisIsNotAnInt_array() {
    Assert::false(instance('int[]', $this));
  }

  #[Test]
  public function emptyArrayIsAnInt_array() {
    Assert::true(instance('int[]', []));
  }

  #[Test]
  public function object_array() {
    Assert::true(instance('lang.unittest.Name[]', [new Name('test'), new Name('test'), new Name('test')]));
  }

  #[Test]
  public function objectArrayWithnull() {
    Assert::false(instance('lang.unittest.Name[]', [new Name('test'), new Name('test'), null]));
  }

  #[Test]
  public function stringMap() {
    Assert::true(instance('[:string]', ['greet' => 'Hello', 'whom' => 'World']));
  }

  #[Test]
  public function intMap() {
    Assert::true(instance('[:int]', ['greet' => 1, 'whom' => 2]));
  }

  #[Test]
  public function intArrayIsNotAnIntMap() {
    Assert::false(instance('[:int]', [1, 2, 3]));
  }

  #[Test]
  public function intIsNotAnIntMap() {
    Assert::false(instance('[:int]', 1));
  }

  #[Test]
  public function thisIsNotAnIntMap() {
    Assert::false(instance('[:int]', $this));
  }

  #[Test]
  public function emptyArrayIsAnIntMap() {
    Assert::true(instance('[:int]', []));
  }

  #[Test]
  public function stringPrimitive() {
    Assert::true(instance('string', 'Hello'));
  }

  #[Test]
  public function nullNotAStringPrimitive() {
    Assert::false(instance('string', null));
  }

  #[Test]
  public function boolPrimitive() {
    Assert::true(instance('bool', true));
  }

  #[Test]
  public function nullNotABoolPrimitive() {
    Assert::false(instance('bool', null));
  }

  #[Test]
  public function doublePrimitive() {
    Assert::true(instance('double', 0.0));
  }

  #[Test]
  public function nullNotADoublePrimitive() {
    Assert::false(instance('double', null));
  }

  #[Test]
  public function intPrimitive() {
    Assert::true(instance('int', 0));
  }

  #[Test]
  public function nullNotAnIntPrimitive() {
    Assert::false(instance('int', null));
  }

  #[Test]
  public function undefinedClassName() {
    Assert::false(class_exists('Undefined_Class', false));
    Assert::false(instance('Undefined_Class', new class() { }));
  }

  #[Test]
  public function fullyQualifiedClassName() {
    Assert::true(instance('lang.Value', new Name('test')));
  }

  #[Test]
  public function interfaces() {
    ClassLoader::defineClass(
      'lang.unittest.RunnableImpl', 
      null,
      [Runnable::class],
      ['run' => function() { }]
    );
    ClassLoader::defineClass(
      'lang.unittest.RunnableImplEx', 
      'lang.unittest.RunnableImpl',
      [],
      []
    );
    
    Assert::true(instance('lang.Runnable', new RunnableImpl()));
    Assert::true(instance('lang.Runnable', new RunnableImplEx()));
    Assert::false(instance('lang.Runnable', new class() { }));
  }

  #[Test]
  public function aStringVectorIsIsItself() {
    Assert::true(instance('lang.unittest.ListOf<string>', create('new lang.unittest.ListOf<string>')));
  }

  #[Test]
  public function aVectorIsNotAStringVector() {
    Assert::false(instance('lang.unittest.ListOf<string>', new ListOf()));
  }

  #[Test]
  public function aStringVectorIsNotAVector() {
    Assert::false(instance(
      'lang.unittest.ListOf',
      create('new lang.unittest.ListOf<string>')
    ));
  }

  #[Test]
  public function anIntVectorIsNotAStringVector() {
    Assert::false(instance(
      'lang.unittest.ListOf<string>',
      create('new lang.unittest.ListOf<int>')
    ));
  }

  #[Test]
  public function aVectorOfIntVectorsIsItself() {
    Assert::true(instance(
      'lang.unittest.ListOf<lang.unittest.ListOf<int>>',
      create('new lang.unittest.ListOf<lang.unittest.ListOf<int>>')
    ));
  }

  #[Test]
  public function aVectorOfIntVectorsIsNotAVectorOfStringVectors() {
    Assert::false(instance(
      'lang.unittest.ListOf<Vector<string>>',
      create('new lang.unittest.ListOf<lang.unittest.ListOf<int>>')
    ));
  }
 
  #[Test]
  public function anIntVectorIsNotAnUndefinedGeneric() {
    Assert::false(instance('Undefined_Class<string>', create('new lang.unittest.ListOf<int>')));
  }

  /** @return var[][] */
  private function genericDictionaries() {
    return [
      [create('new lang.unittest.Lookup<string, lang.Value>')],
      [create('new lang.unittest.Lookup<lang.Value, lang.Value>')],
      [create('new lang.unittest.Lookup<lang.unittest.ListOf<int>, lang.Value>')],
    ];
  }

  #[Test, Values(from: 'genericDictionaries')]
  public function wildcard_check_for_type_parameters($value) {
    Assert::true(instance('lang.unittest.Lookup<?, ?>', $value));
  }

  #[Test, Values(from: 'genericDictionaries')]
  public function wildcard_check_for_type_parameter_with_super_type($value) {
    Assert::true(instance('lang.unittest.IDictionary<?, ?>', $value));
  }

  #[Test]
  public function wildcard_check_for_single_type_parameter_generic() {
    Assert::true(instance(
      'lang.unittest.ListOf<lang.unittest.ListOf<?>>',
      create('new lang.unittest.ListOf<lang.unittest.ListOf<int>>')
    ));
  }

  #[Test]
  public function wildcard_check_for_type_parameters_partial() {
    Assert::true(instance(
      'lang.unittest.Lookup<string, ?>',
      create('new lang.unittest.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function wildcard_check_for_newinstance() {
    Assert::true(instance('util.Filter<?>', newinstance('util.Filter<string>', [], [
      'accept' => fn($e) => true
    ])));
  }

  #[Test]
  public function function_type() {
    Assert::true(instance('function(): var', function() { }));
  }

  #[Test]
  public function function_type_returning_array() {
    Assert::true(instance('function(): var[]', function() { }));
  }

  #[Test]
  public function braced_function_type() {
    Assert::true(instance('(function(): var)', function() { }));
  }

  #[Test]
  public function array_of_function_type() {
    Assert::true(instance('(function(): var)[]', [function() { }]));
  }

  #[Test, Values([1, 'Test'])]
  public function type_union($val) {
    Assert::true(instance('int|string', $val));
  }

  #[Test, Values([1, null])]
  public function nullable($val) {
    Assert::true(instance('?int', $val));
  }

  #[Test, Values(from: 'callables')]
  public function is_callable($val) {
    Assert::true(instance('callable', $val));
  }

  #[Test, Values([[[]], [[1, 2, 3]], [['key' => 'value']],])]
  public function is_array($val) {
    Assert::true(instance('array', $val));
  }

  #[Test, Values(from: 'iterables')]
  public function is_iterable($val) {
    Assert::true(instance('iterable', $val));
  }

  #[Test, Values(from: 'objects')]
  public function is_object($val) {
    Assert::true(instance('object', $val));
  }

  #[Test, Values(from: 'functions')]
  public function closures_are_objects($val) {
    Assert::true(instance('object', $val));
  }

  #[Test]
  public function type_intersection() {
    Assert::true(instance('Countable&Traversable', new \ArrayObject([])));
  }
}