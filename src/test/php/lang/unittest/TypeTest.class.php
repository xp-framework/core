<?php namespace lang\unittest;

use lang\{
  ArrayType,
  Nullable,
  ClassCastException,
  FunctionType,
  IllegalAccessException,
  IllegalStateException,
  MapType,
  Primitive,
  Type,
  XPClass
};
use test\{Assert, Expect, Test, Values};
use util\collections\{HashTable, Vector};

class TypeTest {

  /** @return iterable */
  private function callables() {
    yield ['strlen'];
    yield ['xp::gc'];
    yield [['xp', 'gc']];
    yield [[new Name('test'), 'compareTo']];
    yield [function() { }];
  }

  /** @return iterable */
  private function iterables() {
    yield [[]];
    yield [[1, 2, 3]];
    yield [['key' => 'value']];
    yield [new \ArrayObject(['hello', 'world'])];
    yield [new \ArrayIterator(['hello', 'world'])];
  }

  /** @return iterable */
  private function names() {
    yield ['int, string', ['int', 'string']];
    yield ['int|string, string', ['int|string', 'string']];
    yield ['int|(function(int, string)), string', ['int|(function(int, string))', 'string']];
    yield ['Set<T>, string', ['Set<T>', 'string']];
    yield ['Map<K, V>, string', ['Map<K, V>', 'string']];
    yield ['function(int): string, string', ['function(int): string', 'string']];
    yield ['(function(int, string)), string', ['(function(int, string))', 'string']];
    yield ['(function(Set<T>, string)), string', ['(function(Set<T>, string))', 'string']];
    yield ['(function(int, string))[], string', ['(function(int, string))[]', 'string']];
    yield ['Filter<T>[], string', ['Filter<T>[]', 'string']];
    yield ['Filter<Set<T>>, string', ['Filter<Set<T>>', 'string']];
    yield ['Filter<function(int): string>[], string', ['Filter<function(int): string>[]', 'string']];
  }

  #[Test, Values(['string'])]
  public function stringType($named) {
    Assert::equals(Primitive::$STRING, Type::forName($named));
  }

  #[Test, Values(['int', 'integer'])]
  public function intType($named) {
    Assert::equals(Primitive::$INT, Type::forName($named));
  }

  #[Test, Values(['double', 'float'])]
  public function doubleType($named) {
    Assert::equals(Primitive::$FLOAT, Type::forName($named));
  }

  #[Test, Values(['bool', 'boolean', 'false', 'true'])]
  public function boolType($named) {
    Assert::equals(Primitive::$BOOL, Type::forName($named));
  }

  #[Test, Values(['void', 'null'])]
  public function voidType($named) {
    Assert::equals(Type::$VOID, Type::forName($named));
  }

  #[Test]
  public function varType() {
    Assert::equals(Type::$VAR, Type::forName('var'));
  }

  #[Test, Values(['array'])]
  public function arrayTypeUnion($named) {
    Assert::equals(Type::$ARRAY, Type::forName($named));
  }

  #[Test, Values(['callable'])]
  public function callableTypeUnion($named) {
    Assert::equals(Type::$CALLABLE, Type::forName($named));
  }

  #[Test, Values(['iterable'])]
  public function iterableTypeUnion($named) {
    Assert::equals(Type::$ITERABLE, Type::forName($named));
  }

  #[Test, Values(['object'])]
  public function objectTypeUnion($named) {
    Assert::equals(Type::$OBJECT, Type::forName($named));
  }

  #[Test]
  public function arrayOfString() {
    Assert::equals(ArrayType::forName('string[]'), Type::forName('string[]'));
  }

  #[Test]
  public function mapOfString() {
    Assert::equals(MapType::forName('[:string]'), Type::forName('[:string]'));
  }

  #[Test]
  public function nullableOfString() {
    Assert::equals(Nullable::forName('?string'), Type::forName('?string'));
  }

  #[Test, Values(['lang.unittest.Name', '\lang\unittest\Name', Name::class])]
  public function objectType($name) {
    Assert::equals(XPClass::forName('lang.unittest.Name'), Type::forName($name));
  }

  #[Test]
  public function objectTypeLiteralLoadedIfNecessary() {
    $literal= 'lang\\unittest\\TypeRefByLiteralLoadedOnDemand';

    Type::forName($literal);
    Assert::true(class_exists($literal, false));
  }

  #[Test]
  public function objectTypeLoadedIfNecessary() {
    $literal= 'lang\\unittest\\TypeRefByNameLoadedOnDemand';
    $name= 'lang.unittest.TypeRefByNameLoadedOnDemand';

    Type::forName($name);
    Assert::true(class_exists($literal, false));
  }

  #[Test]
  public function closureType() {
    Assert::equals(new XPClass('Closure'), Type::forName('Closure'));
  }

  #[Test]
  public function generic() {
    Assert::equals(
      XPClass::forName('lang.unittest.Nullable')->newGenericType([Primitive::$STRING]),
      Type::forName('lang.unittest.Nullable<string>')
    );
  }

  #[Test]
  public function genericOfGeneneric() {
    $t= XPClass::forName('lang.unittest.Nullable');
    Assert::equals(
      $t->newGenericType([$t->newGenericType([Primitive::$INT])]), 
      Type::forName('lang.unittest.Nullable<lang.unittest.Nullable<int>>')
    );
  }

  #[Test]
  public function genericObjectType() {
    with ($t= Type::forName('lang.unittest.IDictionary<string, lang.Value>')); {
      Assert::instance(XPClass::class, $t);
      Assert::true($t->isGeneric());
      Assert::equals(XPClass::forName('lang.unittest.IDictionary'), $t->genericDefinition());
      Assert::equals(
        [Primitive::$STRING, XPClass::forName('lang.Value')],
        $t->genericArguments()
      );
    }
  }

  #[Test]
  public function resource_type() {
    Assert::equals(Type::$VAR, Type::forName('resource'));
  }

  #[Test, Values(['function(): var', '(function(): var)'])]
  public function function_type($decl) {
    Assert::equals(new FunctionType([], Type::$VAR), Type::forName($decl));
  }

  #[Test, Values(['function(): int[]', '(function(): int[])'])]
  public function a_function_returning_array_of_int($decl) {
    Assert::equals(new FunctionType([], new ArrayType(Primitive::$INT)), Type::forName($decl));
  }

  #[Test, Values(['[:function(): int]', '[:(function(): int)]'])]
  public function a_map_of_functions_returning_int($decl) {
    Assert::equals(new MapType(new FunctionType([], Primitive::$INT)), Type::forName($decl));
  }

  #[Test]
  public function an_array_of_functions_returning_int() {
    Assert::equals(
      new ArrayType(new FunctionType([], Primitive::$INT)),
      Type::forName('(function(): int)[]')
    );
  }

  #[Test]
  public function an_array_of_arrays_of_functions_returning_int() {
    Assert::equals(
      new ArrayType(new ArrayType(new FunctionType([], Primitive::$INT))),
      Type::forName('(function(): int)[][]')
    );
  }

  #[Test, Expect(IllegalStateException::class), Values([null, ''])]
  public function forName_raises_exception_when_given_empty($value) {
    Type::forName($value);
  }

  /** @return var[] */
  protected function instances() {
    return [$this, null, false, true, '', 0, -1, 0.0, [[]], [['one' => 'two']], $this];
  }

  #[Test, Values(from: 'instances')]
  public function anythingIsAnInstanceOfVar($value) {
    Assert::true(Type::$VAR->isInstance($value));
  }

  #[Test, Values(from: 'instances')]
  public function nothingIsAnInstanceOfVoid($value) {
    Assert::false(Type::$VOID->isInstance($value));
  }

  /** @return var[] */
  protected function types() {
    return [
      typeof($this),
      Type::$VAR,
      Primitive::$BOOL, Primitive::$STRING, Primitive::$INT, Primitive::$FLOAT,
      new ArrayType('var'),
      new MapType('var')
    ];
  }

  #[Test, Values(from: 'types')]
  public function varIsAssignableFromAnything($type) {
    Assert::true(Type::$VAR->isAssignableFrom($type));
  }

  #[Test]
  public function varIsNotAssignableFromVoid() {
    Assert::false(Type::$VAR->isAssignableFrom(Type::$VOID));
  }

  #[Test, Values(from: 'types')]
  public function voidIsAssignableFromNothing($type) {
    Assert::false(Type::$VOID->isAssignableFrom($type));
  }

  #[Test]
  public function voidIsAlsoNotAssignableFromVoid() {
    Assert::false(Type::$VOID->isAssignableFrom(Type::$VOID));
  }

  #[Test, Values(from: 'instances')]
  public function newInstance_of_var($value) {
    Assert::equals($value, Type::$VAR->newInstance($value));
  }

  #[Test, Expect(IllegalAccessException::class), Values(from: 'instances')]
  public function newInstance_of_void($value) {
    Type::$VOID->newInstance($value);
  }

  #[Test, Values(from: 'instances')]
  public function cast_to_var($value) {
    Assert::equals($value, Type::$VAR->cast($value));
  }

  #[Test, Expect(ClassCastException::class), Values(from: 'instances')]
  public function cast_to_void($value) {
    Type::$VOID->cast($value);
  }

  #[Test]
  public function string_type_default() {
    Assert::equals('', Primitive::$STRING->default);
  }

  #[Test]
  public function int_type_default() {
    Assert::equals(0, Primitive::$INT->default);
  }

  #[Test]
  public function double_type_default() {
    Assert::equals(0.0, Primitive::$FLOAT->default);
  }

  #[Test]
  public function bool_type_default() {
    Assert::equals(false, Primitive::$BOOL->default);
  }

  #[Test]
  public function array_type_default() {
    Assert::equals([], (new ArrayType('var'))->default);
  }

  #[Test]
  public function map_type_default() {
    Assert::equals([], (new MapType('var'))->default);
  }

  #[Test]
  public function class_type_default() {
    Assert::equals(null, XPClass::forName('lang.Value')->default);
  }

  #[Test]
  public function var_type_default() {
    Assert::equals(null, Type::$VAR->default);
  }

  #[Test]
  public function void_type_default() {
    Assert::equals(null, Type::$VOID->default);
  }

  #[Test]
  public function native_array_default() {
    Assert::equals([], Type::$ARRAY->default);
  }

  #[Test]
  public function native_callable_default() {
    Assert::equals(null, Type::$CALLABLE->default);
  }

  #[Test]
  public function native_iterable_default() {
    Assert::equals(null, Type::$ITERABLE->default);
  }

  #[Test, Values([[[]], [[1, 2, 3]], [['key' => 'value']]])]
  public function array_type_union_isInstance($value) {
    Assert::true(Type::$ARRAY->isInstance($value));
  }

  #[Test, Values([[[]], [1], [1.5], [true], ['Test'], [[1, 2, 3]], [['key' => 'value']]])]
  public function array_type_union_newInstance_from_array($value) {
    Assert::equals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[Test]
  public function array_type_union_newInstance_without_args() {
    Assert::equals([], Type::$ARRAY->newInstance());
  }

  #[Test, Values(eval: '[Type::$ARRAY, new ArrayType("var"), new MapType("var")]')]
  public function array_type_union_isAssignableFrom_arrays($type) {
    Assert::true(Type::$ARRAY->isAssignableFrom($type));
  }

  #[Test, Values(eval: '[Primitive::$INT, Type::$VOID, new FunctionType([], Type::$VAR)]')]
  public function array_type_union_is_not_assignable_from($type) {
    Assert::false(Type::$ARRAY->isAssignableFrom($type));
  }

  #[Test]
  public function array_type_union_is_not_assignable_from_this() {
    Assert::false(Type::$ARRAY->isAssignableFrom(typeof($this)));
  }

  #[Test, Values([[null], [1], [1.5], [true], ['Test'], [[]], [[1, 2, 3]], [['key' => 'value']]])]
  public function array_type_union_cast($value) {
    Assert::equals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[Test]
  public function array_type_union_cast_null() {
    Assert::equals(null, Type::$ARRAY->cast(null));
  }

  #[Test, Values(from: 'callables')]
  public function callable_type_union_isInstance($value) {
    Assert::true(Type::$CALLABLE->isInstance($value));
  }

  #[Test, Values(from: 'callables')]
  public function callable_type_union_newInstance($value) {
    Assert::equals($value, Type::$CALLABLE->newInstance($value));
  }

  #[Test, Values(from: 'callables')]
  public function callable_type_union_cast($value) {
    Assert::equals($value, Type::$CALLABLE->cast($value));
  }

  #[Test]
  public function callable_type_union_cast_null() {
    Assert::equals(null, Type::$CALLABLE->cast(null));
  }

  #[Test]
  public function callable_type_union_isAssignableFrom_callable() {
    Assert::true(Type::$CALLABLE->isAssignableFrom(Type::$CALLABLE));
  }

  #[Test]
  public function callable_type_union_isAssignableFrom_functions() {
    Assert::true(Type::$CALLABLE->isAssignableFrom(new FunctionType([], Type::$VAR)));
  }

  #[Test, Values(eval: '[Primitive::$INT, Type::$VOID, new ArrayType("var"), new MapType("var")]')]
  public function callable_type_union_is_not_assignable_from($type) {
    Assert::false(Type::$CALLABLE->isAssignableFrom($type));
  }

  #[Test]
  public function callable_type_union_is_not_assignable_from_this() {
    Assert::false(Type::$CALLABLE->isAssignableFrom(typeof($this)));
  }

  #[Test, Values(from: 'iterables')]
  public function iterable_type_union_isInstance($value) {
    Assert::true(Type::$ITERABLE->isInstance($value));
  }

  #[Test]
  public function iterable_type_union_generator_isInstance() {
    $gen= function() { yield 'Test'; };
    Assert::true(Type::$ITERABLE->isInstance($gen()));
  }

  #[Test, Values(from: 'iterables')]
  public function iterable_type_union_newInstance($value) {
    Assert::equals($value, Type::$ITERABLE->newInstance($value));
  }

  #[Test, Values(from: 'iterables')]
  public function iterable_type_union_cast($value) {
    Assert::equals($value, Type::$ITERABLE->cast($value));
  }

  #[Test]
  public function iterable_type_union_cast_null() {
    Assert::null(Type::$ITERABLE->cast(null));
  }
  #[Test, Values(eval: '[[new Name("test")], [new \ArrayObject([])]]')]
  public function object_type_union_isInstance($value) {
    Assert::true(Type::$OBJECT->isInstance($value));
  }

  #[Test, Values(eval: '[[function() { }], [function() { yield "Test"; }]]')]
  public function closures_are_instances_of_the_object_type_union($value) {
    Assert::true(Type::$OBJECT->isInstance($value));
  }

  #[Test, Values(eval: '[[null], [new Name("test")], [new \ArrayObject([])]]')]
  public function object_type_union_cast($value) {
    Assert::equals($value, Type::$OBJECT->cast($value));
  }

  #[Test, Values(eval: '[[new Name("test")], [new \ArrayObject([])]]')]
  public function object_type_union_newInstance($value) {
    Assert::instance(typeof($value), Type::$OBJECT->newInstance($value));
  }

  #[Test]
  public function object_type_union_isAssignableFrom_self() {
    Assert::true(Type::$OBJECT->isAssignableFrom(Type::$OBJECT));
  }

  #[Test]
  public function object_type_union_isAssignableFrom_this_class() {
    Assert::true(Type::$OBJECT->isAssignableFrom(typeof($this)));
  }

  #[Test, Values(eval: '[Primitive::$INT, Type::$VOID, new ArrayType("var"), new MapType("var")]')]
  public function object_type_union_is_not_assignable_from($type) {
    Assert::false(Type::$OBJECT->isAssignableFrom($type));
  }

  #[Test, Values(eval: '[[function() { }, true], [function(int $arg) { }, false]]')]
  public function function_parameter_count($fixture, $expected) {
    Assert::equals($expected, Type::forName('function(): var')->isInstance($fixture));
  }

  #[Test, Values(eval: '[[function() { }, true], [function(int $arg) { }, true]]')]
  public function function_parameter_placeholder($fixture, $expected) {
    Assert::equals($expected, Type::forName('function(?): var')->isInstance($fixture));
  }

  #[Test, Values(eval: '[[function(string $arg) { }, true], [function(int $arg) { }, false]]')]
  public function function_parameter_type($fixture, $expected) {
    Assert::equals($expected, Type::forName('function(string): var')->isInstance($fixture));
  }

  #[Test, Values(eval: '[[function(): string { }, true], [function(): int { }, false]]')]
  public function function_return_type($fixture, $expected) {
    Assert::equals($expected, Type::forName('function(): string')->isInstance($fixture));
  }

  #[Test, Values(from: 'names')]
  public function split($names, $expected) {
    Assert::equals($expected, [...Type::split($names, ',')]);
  }
}