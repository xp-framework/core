<?php namespace net\xp_framework\unittest\reflection;

use lang\{
  ArrayType,
  NullableType,
  ClassCastException,
  FunctionType,
  IllegalAccessException,
  IllegalStateException,
  MapType,
  Primitive,
  Type,
  XPClass
};
use net\xp_framework\unittest\Name;
use unittest\{Expect, Test, TestCase, Values};
use util\collections\{HashTable, Vector};

class TypeTest extends TestCase {

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

  #[Test, Values(['string'])]
  public function stringType($named) {
    $this->assertEquals(Primitive::$STRING, Type::forName($named));
  }

  #[Test, Values(['int', 'integer'])]
  public function intType($named) {
    $this->assertEquals(Primitive::$INT, Type::forName($named));
  }

  #[Test, Values(['double', 'float'])]
  public function doubleType($named) {
    $this->assertEquals(Primitive::$FLOAT, Type::forName($named));
  }

  #[Test, Values(['bool', 'boolean', 'false'])]
  public function boolType($named) {
    $this->assertEquals(Primitive::$BOOL, Type::forName($named));
  }

  #[Test]
  public function voidType() {
    $this->assertEquals(Type::$VOID, Type::forName('void'));
  }

  #[Test]
  public function varType() {
    $this->assertEquals(Type::$VAR, Type::forName('var'));
  }

  #[Test, Values(['array'])]
  public function arrayTypeUnion($named) {
    $this->assertEquals(Type::$ARRAY, Type::forName($named));
  }

  #[Test, Values(['callable'])]
  public function callableTypeUnion($named) {
    $this->assertEquals(Type::$CALLABLE, Type::forName($named));
  }

  #[Test, Values(['iterable'])]
  public function iterableTypeUnion($named) {
    $this->assertEquals(Type::$ITERABLE, Type::forName($named));
  }

  #[Test, Values(['object'])]
  public function objectTypeUnion($named) {
    $this->assertEquals(Type::$OBJECT, Type::forName($named));
  }

  #[Test]
  public function arrayOfString() {
    $this->assertEquals(ArrayType::forName('string[]'), Type::forName('string[]'));
  }

  #[Test]
  public function mapOfString() {
    $this->assertEquals(MapType::forName('[:string]'), Type::forName('[:string]'));
  }

  #[Test]
  public function nullableOfString() {
    $this->assertEquals(NullableType::forName('?string'), Type::forName('?string'));
  }

  #[Test, Values(['net.xp_framework.unittest.Name', '\net\xp_framework\unittest\Name', Name::class])]
  public function objectType($name) {
    $this->assertEquals(XPClass::forName('net.xp_framework.unittest.Name'), Type::forName($name));
  }

  #[Test]
  public function objectTypeLiteralLoadedIfNecessary() {
    $literal= 'net\\xp_framework\\unittest\\reflection\\TypeRefByLiteralLoadedOnDemand';

    Type::forName($literal);
    $this->assertTrue(class_exists($literal, false));
  }

  #[Test]
  public function objectTypeLoadedIfNecessary() {
    $literal= 'net\\xp_framework\\unittest\\reflection\\TypeRefByNameLoadedOnDemand';
    $name= 'net.xp_framework.unittest.reflection.TypeRefByNameLoadedOnDemand';

    Type::forName($name);
    $this->assertTrue(class_exists($literal, false));
  }

  #[Test]
  public function closureType() {
    $this->assertEquals(new XPClass('Closure'), Type::forName('Closure'));
  }

  #[Test]
  public function generic() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Nullable')->newGenericType([Primitive::$STRING]),
      Type::forName('net.xp_framework.unittest.core.generics.Nullable<string>')
    );
  }

  #[Test]
  public function genericOfGeneneric() {
    $t= XPClass::forName('net.xp_framework.unittest.core.generics.Nullable');
    $this->assertEquals(
      $t->newGenericType([$t->newGenericType([Primitive::$INT])]), 
      Type::forName('net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.generics.Nullable<int>>')
    );
  }

  #[Test]
  public function genericObjectType() {
    with ($t= Type::forName('net.xp_framework.unittest.core.generics.IDictionary<string, lang.Value>')); {
      $this->assertInstanceOf(XPClass::class, $t);
      $this->assertTrue($t->isGeneric());
      $this->assertEquals(XPClass::forName('net.xp_framework.unittest.core.generics.IDictionary'), $t->genericDefinition());
      $this->assertEquals(
        [Primitive::$STRING, XPClass::forName('lang.Value')],
        $t->genericArguments()
      );
    }
  }

  #[Test]
  public function resource_type() {
    $this->assertEquals(Type::$VAR, Type::forName('resource'));
  }

  #[Test, Values(['function(): var', '(function(): var)'])]
  public function function_type($decl) {
    $this->assertEquals(new FunctionType([], Type::$VAR), Type::forName($decl));
  }

  #[Test, Values(['function(): int[]', '(function(): int[])'])]
  public function a_function_returning_array_of_int($decl) {
    $this->assertEquals(new FunctionType([], new ArrayType(Primitive::$INT)), Type::forName($decl));
  }

  #[Test, Values(['[:function(): int]', '[:(function(): int)]'])]
  public function a_map_of_functions_returning_int($decl) {
    $this->assertEquals(new MapType(new FunctionType([], Primitive::$INT)), Type::forName($decl));
  }

  #[Test]
  public function an_array_of_functions_returning_int() {
    $this->assertEquals(
      new ArrayType(new FunctionType([], Primitive::$INT)),
      Type::forName('(function(): int)[]')
    );
  }

  #[Test]
  public function an_array_of_arrays_of_functions_returning_int() {
    $this->assertEquals(
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

  #[Test, Values('instances')]
  public function anythingIsAnInstanceOfVar($value) {
    $this->assertTrue(Type::$VAR->isInstance($value));
  }

  #[Test, Values('instances')]
  public function nothingIsAnInstanceOfVoid($value) {
    $this->assertFalse(Type::$VOID->isInstance($value));
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

  #[Test, Values('types')]
  public function varIsAssignableFromAnything($type) {
    $this->assertTrue(Type::$VAR->isAssignableFrom($type));
  }

  #[Test]
  public function varIsNotAssignableFromVoid() {
    $this->assertFalse(Type::$VAR->isAssignableFrom(Type::$VOID));
  }

  #[Test, Values('types')]
  public function voidIsAssignableFromNothing($type) {
    $this->assertFalse(Type::$VOID->isAssignableFrom($type));
  }

  #[Test]
  public function voidIsAlsoNotAssignableFromVoid() {
    $this->assertFalse(Type::$VOID->isAssignableFrom(Type::$VOID));
  }

  #[Test, Values('instances')]
  public function newInstance_of_var($value) {
    $this->assertEquals($value, Type::$VAR->newInstance($value));
  }

  #[Test, Expect(IllegalAccessException::class), Values('instances')]
  public function newInstance_of_void($value) {
    Type::$VOID->newInstance($value);
  }

  #[Test, Values('instances')]
  public function cast_to_var($value) {
    $this->assertEquals($value, Type::$VAR->cast($value));
  }

  #[Test, Expect(ClassCastException::class), Values('instances')]
  public function cast_to_void($value) {
    Type::$VOID->cast($value);
  }

  #[Test]
  public function string_type_default() {
    $this->assertEquals('', Primitive::$STRING->default);
  }

  #[Test]
  public function int_type_default() {
    $this->assertEquals(0, Primitive::$INT->default);
  }

  #[Test]
  public function double_type_default() {
    $this->assertEquals(0.0, Primitive::$FLOAT->default);
  }

  #[Test]
  public function bool_type_default() {
    $this->assertEquals(false, Primitive::$BOOL->default);
  }

  #[Test]
  public function array_type_default() {
    $this->assertEquals([], (new ArrayType('var'))->default);
  }

  #[Test]
  public function map_type_default() {
    $this->assertEquals([], (new MapType('var'))->default);
  }

  #[Test]
  public function class_type_default() {
    $this->assertEquals(null, XPClass::forName('lang.Value')->default);
  }

  #[Test]
  public function var_type_default() {
    $this->assertEquals(null, Type::$VAR->default);
  }

  #[Test]
  public function void_type_default() {
    $this->assertEquals(null, Type::$VOID->default);
  }

  #[Test]
  public function native_array_default() {
    $this->assertEquals([], Type::$ARRAY->default);
  }

  #[Test]
  public function native_callable_default() {
    $this->assertEquals(null, Type::$CALLABLE->default);
  }

  #[Test]
  public function native_iterable_default() {
    $this->assertEquals(null, Type::$ITERABLE->default);
  }

  #[Test, Values([[[]], [[1, 2, 3]], [['key' => 'value']]])]
  public function array_type_union_isInstance($value) {
    $this->assertTrue(Type::$ARRAY->isInstance($value));
  }

  #[Test, Values([[[]], [1], [1.5], [true], ['Test'], [[1, 2, 3]], [['key' => 'value']]])]
  public function array_type_union_newInstance_from_array($value) {
    $this->assertEquals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[Test]
  public function array_type_union_newInstance_without_args() {
    $this->assertEquals([], Type::$ARRAY->newInstance());
  }

  #[Test, Values(eval: '[Type::$ARRAY, new ArrayType("var"), new MapType("var")]')]
  public function array_type_union_isAssignableFrom_arrays($type) {
    $this->assertTrue(Type::$ARRAY->isAssignableFrom($type));
  }

  #[Test, Values(eval: '[Primitive::$INT, Type::$VOID, new FunctionType([], Type::$VAR)]')]
  public function array_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$ARRAY->isAssignableFrom($type));
  }

  #[Test]
  public function array_type_union_is_not_assignable_from_this() {
    $this->assertFalse(Type::$ARRAY->isAssignableFrom(typeof($this)));
  }

  #[Test, Values([[null], [1], [1.5], [true], ['Test'], [[]], [[1, 2, 3]], [['key' => 'value']]])]
  public function array_type_union_cast($value) {
    $this->assertEquals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[Test]
  public function array_type_union_cast_null() {
    $this->assertEquals(null, Type::$ARRAY->cast(null));
  }

  #[Test, Values('callables')]
  public function callable_type_union_isInstance($value) {
    $this->assertTrue(Type::$CALLABLE->isInstance($value));
  }

  #[Test, Values('callables')]
  public function callable_type_union_newInstance($value) {
    $this->assertEquals($value, Type::$CALLABLE->newInstance($value));
  }

  #[Test, Values('callables')]
  public function callable_type_union_cast($value) {
    $this->assertEquals($value, Type::$CALLABLE->cast($value));
  }

  #[Test]
  public function callable_type_union_cast_null() {
    $this->assertEquals(null, Type::$CALLABLE->cast(null));
  }

  #[Test]
  public function callable_type_union_isAssignableFrom_callable() {
    $this->assertTrue(Type::$CALLABLE->isAssignableFrom(Type::$CALLABLE));
  }

  #[Test]
  public function callable_type_union_isAssignableFrom_functions() {
    $this->assertTrue(Type::$CALLABLE->isAssignableFrom(new FunctionType([], Type::$VAR)));
  }

  #[Test, Values(eval: '[Primitive::$INT, Type::$VOID, new ArrayType("var"), new MapType("var")]')]
  public function callable_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$CALLABLE->isAssignableFrom($type));
  }

  #[Test]
  public function callable_type_union_is_not_assignable_from_this() {
    $this->assertFalse(Type::$CALLABLE->isAssignableFrom(typeof($this)));
  }

  #[Test, Values('iterables')]
  public function iterable_type_union_isInstance($value) {
    $this->assertTrue(Type::$ITERABLE->isInstance($value));
  }

  #[Test]
  public function iterable_type_union_generator_isInstance() {
    $gen= function() { yield 'Test'; };
    $this->assertTrue(Type::$ITERABLE->isInstance($gen()));
  }

  #[Test, Values('iterables')]
  public function iterable_type_union_newInstance($value) {
    $this->assertEquals($value, Type::$ITERABLE->newInstance($value));
  }

  #[Test, Values('iterables')]
  public function iterable_type_union_cast($value) {
    $this->assertEquals($value, Type::$ITERABLE->cast($value));
  }

  #[Test]
  public function iterable_type_union_cast_null() {
    $this->assertNull(Type::$ITERABLE->cast(null));
  }
  #[Test, Values(eval: '[[new Name("test")], [new \ArrayObject([])]]')]
  public function object_type_union_isInstance($value) {
    $this->assertTrue(Type::$OBJECT->isInstance($value));
  }

  #[Test, Values(eval: '[[function() { }], [function() { yield "Test"; }]]')]
  public function closures_are_instances_of_the_object_type_union($value) {
    $this->assertTrue(Type::$OBJECT->isInstance($value));
  }

  #[Test, Values(eval: '[[null], [new Name("test")], [new \ArrayObject([])]]')]
  public function object_type_union_cast($value) {
    $this->assertEquals($value, Type::$OBJECT->cast($value));
  }

  #[Test, Values(eval: '[[new Name("test")], [new \ArrayObject([])]]')]
  public function object_type_union_newInstance($value) {
    $this->assertInstanceOf(typeof($value), Type::$OBJECT->newInstance($value));
  }

  #[Test]
  public function object_type_union_isAssignableFrom_self() {
    $this->assertTrue(Type::$OBJECT->isAssignableFrom(Type::$OBJECT));
  }

  #[Test]
  public function object_type_union_isAssignableFrom_this_class() {
    $this->assertTrue(Type::$OBJECT->isAssignableFrom(typeof($this)));
  }

  #[Test, Values(eval: '[Primitive::$INT, Type::$VOID, new ArrayType("var"), new MapType("var")]')]
  public function object_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$OBJECT->isAssignableFrom($type));
  }

  #[Test, Values(eval: '[[function() { }, true], [function(int $arg) { }, false]]')]
  public function function_parameter_count($fixture, $expected) {
    $this->assertEquals($expected, Type::forName('function(): var')->isInstance($fixture));
  }

  #[Test, Values(eval: '[[function() { }, true], [function(int $arg) { }, true]]')]
  public function function_parameter_placeholder($fixture, $expected) {
    $this->assertEquals($expected, Type::forName('function(?): var')->isInstance($fixture));
  }

  #[Test, Values(eval: '[[function(string $arg) { }, true], [function(int $arg) { }, false]]')]
  public function function_parameter_type($fixture, $expected) {
    $this->assertEquals($expected, Type::forName('function(string): var')->isInstance($fixture));
  }

  #[Test, Values(eval: '[[function(): string { }, true], [function(): int { }, false]]')]
  public function function_return_type($fixture, $expected) {
    $this->assertEquals($expected, Type::forName('function(): string')->isInstance($fixture));
  }
}