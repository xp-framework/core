<?php namespace lang\unittest;

use lang\{IllegalArgumentException, Type, Primitive};
use test\{Assert, Expect, Test, Values};
use util\Binford;

class GenericsTest {

  #[Test]
  public function definition_is_generic() {
    Assert::false(Type::forName('util.Binford')->isGenericDefinition());
    Assert::true(Type::forName('lang.unittest.Lookup')->isGenericDefinition());
  }

  #[Test]
  public function generic_components() {
    Assert::equals(['T'], Type::forName('lang.unittest.ListOf')->genericComponents());
    Assert::equals(['K', 'V'], Type::forName('lang.unittest.Lookup')->genericComponents());
  }

  #[Test]
  public function type_is_generic() {
    Assert::false(Type::forName('util.Binford')->isGeneric());
    Assert::true(Type::forName('lang.unittest.Lookup<int, util.Binford>')->isGeneric());
  }

  #[Test]
  public function type_arguments() {
    Assert::equals(
      [Primitive::$INT, Type::forName('lang.unittest.ListOf<string>')],
      Type::forName('lang.unittest.Lookup<int, lang.unittest.ListOf<string>>')->genericArguments()
    );
  }

  #[Test, Values([
    'lang.unittest.ListOf<string>',
    'lang.unittest.ListOf<int|string>',
    'lang.unittest.Lookup<int, util.Binford>',
    'lang.unittest.ListOf<lang.unittest.Lookup<int, util.Binford>>',
  ])]
  public function creates_instances($type) {
    Assert::instance($type, create("new {$type}"));
  }

  #[Test]
  public function create() {
    Assert::equals(0, create('new lang.unittest.Lookup<int, util.Binford>')->size());
  }

  #[Test]
  public function create_empty() {
    Assert::equals([], create('new lang.unittest.ListOf<string>')->elements());
  }

  #[Test]
  public function create_varargs() {
    Assert::equals(
      ['Hello', 'World'],
      create('new lang.unittest.ListOf<string>', 'Hello', 'World')->elements()
    );
  }

  #[Test]
  public function public_member_accessible() {
    Assert::equals(
      ['Hello', 'World'],
      create('new lang.unittest.ListOf<string>', 'Hello', 'World')->elements
    );
  }

  #[Test]
  public function pass_argument() {
    $fixture= create('new lang.unittest.Lookup<string, util.Binford>');
    $fixture->put('power', new Binford(6100));

    Assert::equals(new Binford(6100), $fixture->get('power'));
  }

  #[Test]
  public function pass_array_argument() {
    $fixture= create('new lang.unittest.ListOf<string>', 'Hello');
    $fixture->extend(['World', '!']);

    Assert::equals(['Hello', 'World', '!'], $fixture->elements);
  }

  #[Test]
  public function pass_nullable() {
    $fixture= create('new lang.unittest.ListOf<?string>')
      ->append('Hello')
      ->append(null)
    ;

    Assert::equals(['Hello', null], $fixture->elements);
  }

  #[Test]
  public function pass_union() {
    $fixture= create('new lang.unittest.ListOf<int|float>')
      ->append(1)
      ->append(1.5)
    ;

    Assert::equals([1, 1.5], $fixture->elements);
  }

  #[Test, Expect(IllegalArgumentException::class), Values([[[1]], [['Test', 1]], [[null, 'Test']]])]
  public function pass_invalid($arguments) {
    create('new lang.unittest.ListOf<string>', 'Hello')->extend($arguments);
  }

  #[Test, Expect(IllegalArgumentException::class), Values([[[1]], [['Test', 1]], [[null, 'Test']]])]
  public function pass_invalid_varargs($arguments) {
    create('new lang.unittest.ListOf<string>', ...$arguments);
  }

  #[Test]
  public function invoke_generic_method() {
    $fixture= create('new lang.unittest.ListOf<string>', 'Hello', 'World!');
    $mapped= $fixture->{'map<int>'}('strlen');

    Assert::instance('lang.unittest.ListOf<int>', $mapped);
    Assert::equals([5, 6], $mapped->elements);
  }

  #[Test]
  public function generic_method_components() {
    $fixture= create('new lang.unittest.Lookup<string, string>');
    $fixture->put('greeting', 'Hello');
    $fixture->put('person', 'Tester');
    $mapped= $fixture->{'map<int>'}('strlen');

    Assert::instance('lang.unittest.Lookup<string, int>', $mapped);
    Assert::equals([5, 6], $mapped->values());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function generic_method_invalid_argument() {
    create('new lang.unittest.ListOf<string>')->{'map<int>'}(null);
  }
}