<?php namespace net\xp_framework\unittest\reflection;

use lang\Object;
use lang\Type;
use lang\Primitive;
use lang\ArrayType;
use lang\FunctionType;
use lang\MapType;
use lang\XPClass;
use util\collections\Vector;
use util\collections\HashTable;
use lang\IllegalStateException;
use lang\IllegalAccessException;
use lang\ClassCastException;

class TypeTest extends \unittest\TestCase {

  #[@test]
  public function stringType() {
    $this->assertEquals(Primitive::$STRING, Type::forName('string'));
  }

  #[@test, @values(['int', 'integer'])]
  public function intType($named) {
    $this->assertEquals(Primitive::$INT, Type::forName($named));
  }

  #[@test, @values(['double', 'float'])]
  public function doubleType($named) {
    $this->assertEquals(Primitive::$DOUBLE, Type::forName($named));
  }

  #[@test, @values(['bool', 'boolean'])]
  public function boolType($named) {
    $this->assertEquals(Primitive::$BOOL, Type::forName($named));
  }

  #[@test]
  public function voidType() {
    $this->assertEquals(Type::$VOID, Type::forName('void'));
  }

  #[@test]
  public function varType() {
    $this->assertEquals(Type::$VAR, Type::forName('var'));
  }

  #[@test]
  public function arrayTypeUnion() {
    $this->assertEquals(Type::$ARRAY, Type::forName('array'));
  }

  #[@test]
  public function callableTypeUnion() {
    $this->assertEquals(Type::$CALLABLE, Type::forName('callable'));
  }

  #[@test]
  public function iterableTypeUnion() {
    $this->assertEquals(Type::$ITERABLE, Type::forName('iterable'));
  }

  #[@test]
  public function objectTypeUnion() {
    $this->assertEquals(Type::$OBJECT, Type::forName('object'));
  }

  #[@test]
  public function arrayOfString() {
    $this->assertEquals(ArrayType::forName('string[]'), Type::forName('string[]'));
  }

  #[@test]
  public function mapOfString() {
    $this->assertEquals(MapType::forName('[:string]'), Type::forName('[:string]'));
  }

  #[@test, @values(['lang.Object', Object::class, '\\lang\\Object'])]
  public function objectType($name) {
    $this->assertEquals(XPClass::forName('lang.Object'), Type::forName($name));
  }

  #[@test]
  public function objectTypeLiteralLoadedIfNecessary() {
    $literal= 'net\\xp_framework\\unittest\\reflection\\TypeRefByLiteralLoadedOnDemand';

    Type::forName($literal);
    $this->assertTrue(class_exists($literal, false));
  }

  #[@test]
  public function objectTypeLoadedIfNecessary() {
    $literal= 'net\\xp_framework\\unittest\\reflection\\TypeRefByNameLoadedOnDemand';
    $name= 'net.xp_framework.unittest.reflection.TypeRefByNameLoadedOnDemand';

    Type::forName($name);
    $this->assertTrue(class_exists($literal, false));
  }

  #[@test]
  public function closureType() {
    $this->assertEquals(new XPClass('Closure'), Type::forName('Closure'));
  }

  #[@test]
  public function generic() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Nullable')->newGenericType([Primitive::$STRING]),
      Type::forName('net.xp_framework.unittest.core.generics.Nullable<string>')
    );
  }

  #[@test]
  public function genericOfGeneneric() {
    $t= XPClass::forName('net.xp_framework.unittest.core.generics.Nullable');
    $this->assertEquals(
      $t->newGenericType([$t->newGenericType([Primitive::$INT])]), 
      Type::forName('net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.generics.Nullable<int>>')
    );
  }

  #[@test]
  public function genericObjectType() {
    with ($t= Type::forName('net.xp_framework.unittest.core.generics.IDictionary<string, lang.Object>')); {
      $this->assertInstanceOf(XPClass::class, $t);
      $this->assertTrue($t->isGeneric());
      $this->assertEquals(XPClass::forName('net.xp_framework.unittest.core.generics.IDictionary'), $t->genericDefinition());
      $this->assertEquals(
        [Primitive::$STRING, XPClass::forName('lang.Object')],
        $t->genericArguments()
      );
    }
  }

  #[@test]
  public function resource_type() {
    $this->assertEquals(Type::$VAR, Type::forName('resource'));
  }

  #[@test, @values([
  #  'function(): var',
  #  '(function(): var)'
  #])]
  public function function_type($decl) {
    $this->assertEquals(new FunctionType([], Type::$VAR), Type::forName($decl));
  }

  #[@test, @values([
  #  'function(): int[]',
  #  '(function(): int[])'
  #])]
  public function a_function_returning_array_of_int($decl) {
    $this->assertEquals(new FunctionType([], new ArrayType(Primitive::$INT)), Type::forName($decl));
  }

  #[@test, @values([
  #  '[:function(): int]',
  #  '[:(function(): int)]'
  #])]
  public function a_map_of_functions_returning_int($decl) {
    $this->assertEquals(new MapType(new FunctionType([], Primitive::$INT)), Type::forName($decl));
  }

  #[@test]
  public function an_array_of_functions_returning_int() {
    $this->assertEquals(
      new ArrayType(new FunctionType([], Primitive::$INT)),
      Type::forName('(function(): int)[]')
    );
  }

  #[@test]
  public function an_array_of_arrays_of_functions_returning_int() {
    $this->assertEquals(
      new ArrayType(new ArrayType(new FunctionType([], Primitive::$INT))),
      Type::forName('(function(): int)[][]')
    );
  }

  #[@test, @expect(IllegalStateException::class), @values([null, ''])]
  public function forName_raises_exception_when_given_empty($value) {
    Type::forName($value);
  }

  /** @return var[] */
  protected function instances() {
    return [$this, null, false, true, '', 0, -1, 0.0, [[]], [['one' => 'two']], $this];
  }

  #[@test, @values('instances')]
  public function anythingIsAnInstanceOfVar($value) {
    $this->assertTrue(Type::$VAR->isInstance($value));
  }

  #[@test, @values('instances')]
  public function nothingIsAnInstanceOfVoid($value) {
    $this->assertFalse(Type::$VOID->isInstance($value));
  }

  /** @return var[] */
  protected function types() {
    return [
      $this->getClass(),
      Type::$VAR,
      Primitive::$BOOL, Primitive::$STRING, Primitive::$INT, Primitive::$DOUBLE,
      new ArrayType('var'),
      new MapType('var')
    ];
  }

  #[@test, @values('types')]
  public function varIsAssignableFromAnything($type) {
    $this->assertTrue(Type::$VAR->isAssignableFrom($type));
  }

  #[@test]
  public function varIsNotAssignableFromVoid() {
    $this->assertFalse(Type::$VAR->isAssignableFrom(Type::$VOID));
  }

  #[@test, @values('types')]
  public function voidIsAssignableFromNothing($type) {
    $this->assertFalse(Type::$VOID->isAssignableFrom($type));
  }

  #[@test]
  public function voidIsAlsoNotAssignableFromVoid() {
    $this->assertFalse(Type::$VOID->isAssignableFrom(Type::$VOID));
  }

  #[@test, @values('instances')]
  public function newInstance_of_var($value) {
    $this->assertEquals($value, Type::$VAR->newInstance($value));
  }

  #[@test, @expect(IllegalAccessException::class), @values('instances')]
  public function newInstance_of_void($value) {
    Type::$VOID->newInstance($value);
  }

  #[@test, @values('instances')]
  public function cast_to_var($value) {
    $this->assertEquals($value, Type::$VAR->cast($value));
  }

  #[@test, @expect(ClassCastException::class), @values('instances')]
  public function cast_to_void($value) {
    Type::$VOID->cast($value);
  }

  #[@test]
  public function string_type_default() {
    $this->assertEquals('', Primitive::$STRING->default);
  }

  #[@test]
  public function int_type_default() {
    $this->assertEquals(0, Primitive::$INT->default);
  }

  #[@test]
  public function double_type_default() {
    $this->assertEquals(0.0, Primitive::$DOUBLE->default);
  }

  #[@test]
  public function bool_type_default() {
    $this->assertEquals(false, Primitive::$BOOL->default);
  }

  #[@test]
  public function array_type_default() {
    $this->assertEquals([], (new ArrayType('var'))->default);
  }

  #[@test]
  public function map_type_default() {
    $this->assertEquals([], (new MapType('var'))->default);
  }

  #[@test]
  public function class_type_default() {
    $this->assertEquals(null, XPClass::forName('lang.Object')->default);
  }

  #[@test]
  public function var_type_default() {
    $this->assertEquals(null, Type::$VAR->default);
  }

  #[@test]
  public function void_type_default() {
    $this->assertEquals(null, Type::$VOID->default);
  }

  #[@test]
  public function native_array_default() {
    $this->assertEquals([], Type::$ARRAY->default);
  }

  #[@test]
  public function native_callable_default() {
    $this->assertEquals(null, Type::$CALLABLE->default);
  }

  #[@test]
  public function native_iterable_default() {
    $this->assertEquals(null, Type::$ITERABLE->default);
  }

  #[@test, @values([
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']]
  #])]
  public function array_type_union_isInstance($value) {
    $this->assertTrue(Type::$ARRAY->isInstance($value));
  }

  #[@test, @values([
  #  [[]],
  #  [1], [1.5], [true], ['Test'],
  #  [[1, 2, 3]],
  #  [['key' => 'value']]
  #])]
  public function array_type_union_newInstance_from_array($value) {
    $this->assertEquals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[@test]
  public function array_type_union_newInstance_without_args() {
    $this->assertEquals([], Type::$ARRAY->newInstance());
  }

  #[@test, @values([Type::$ARRAY, new ArrayType('var'), new MapType('var')])]
  public function array_type_union_isAssignableFrom_arrays($type) {
    $this->assertTrue(Type::$ARRAY->isAssignableFrom($type));
  }

  #[@test, @values([Primitive::$INT, Type::$VOID, new FunctionType([], Type::$VAR)])]
  public function array_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$ARRAY->isAssignableFrom($type));
  }

  #[@test]
  public function array_type_union_is_not_assignable_from_this() {
    $this->assertFalse(Type::$ARRAY->isAssignableFrom($this->getClass()));
  }

  #[@test, @values([
  #  [null],
  #  [1], [1.5], [true], ['Test'],
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']]
  #])]
  public function array_type_union_cast($value) {
    $this->assertEquals((array)$value, Type::$ARRAY->newInstance($value));
  }

  #[@test]
  public function array_type_union_cast_null() {
    $this->assertEquals(null, Type::$ARRAY->cast(null));
  }

  #[@test, @values([
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'equals']],
  #  [function() { }]
  #])]
  public function callable_type_union_isInstance($value) {
    $this->assertTrue(Type::$CALLABLE->isInstance($value));
  }

  #[@test, @values([
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'equals']],
  #  [function() { }]
  #])]
  public function callable_type_union_newInstance($value) {
    $this->assertEquals($value, Type::$CALLABLE->newInstance($value));
  }

  #[@test, @values([
  #  [null],
  #  ['strlen'],
  #  ['xp::gc'],
  #  [['xp', 'gc']],
  #  [[new Object(), 'equals']],
  #  [function() { }]
  #])]
  public function callable_type_union_cast($value) {
    $this->assertEquals($value, Type::$CALLABLE->cast($value));
  }

  #[@test]
  public function callable_type_union_cast_null() {
    $this->assertEquals(null, Type::$CALLABLE->cast(null));
  }

  #[@test, @values([Type::$CALLABLE, new FunctionType([], Type::$VAR)])]
  public function callable_type_union_isAssignableFrom_functions($type) {
    $this->assertTrue(Type::$CALLABLE->isAssignableFrom($type));
  }

  #[@test, @values([Primitive::$INT, Type::$VOID, new ArrayType('var'), new MapType('var')])]
  public function callable_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$CALLABLE->isAssignableFrom($type));
  }

  #[@test]
  public function callable_type_union_is_not_assignable_from_this() {
    $this->assertFalse(Type::$CALLABLE->isAssignableFrom($this->getClass()));
  }

  #[@test, @values([
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']],
  #  [new \ArrayObject(['hello', 'world'])],
  #  [new \ArrayIterator(['hello', 'world'])]
  #])]
  public function iterable_type_union_isInstance($value) {
    $this->assertTrue(Type::$ITERABLE->isInstance($value));
  }

  #[@test]
  public function iterable_type_union_generator_isInstance() {
    $gen= function() { yield 'Test'; };
    $this->assertTrue(Type::$ITERABLE->isInstance($gen()));
  }

  #[@test, @values([
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']],
  #  [new \ArrayObject(['hello', 'world'])],
  #  [new \ArrayIterator(['hello', 'world'])]
  #])]
  public function iterable_type_union_newInstance($value) {
    $this->assertEquals($value, Type::$ITERABLE->newInstance($value));
  }

  #[@test, @values([
  #  [null],
  #  [[]],
  #  [[1, 2, 3]],
  #  [['key' => 'value']],
  #  [new \ArrayObject(['hello', 'world'])],
  #  [new \ArrayIterator(['hello', 'world'])]
  #])]
  public function iterable_type_union_cast($value) {
    $this->assertEquals($value, Type::$ITERABLE->cast($value));
  }

  #[@test, @values([
  #  [new Object()],
  #  [new \ArrayObject([])]
  #])]
  public function object_type_union_isInstance($value) {
    $this->assertTrue(Type::$OBJECT->isInstance($value));
  }

  #[@test, @values([
  #  [function() { }],
  #  [function() { yield 'Test'; }]
  #])]
  public function closures_are_not_instances_of_the_object_type_union($value) {
    $this->assertFalse(Type::$OBJECT->isInstance($value));
  }

  #[@test, @values([
  #  [null],
  #  [new Object()],
  #  [new \ArrayObject([])]
  #])]
  public function object_type_union_cast($value) {
    $this->assertEquals($value, Type::$OBJECT->cast($value));
  }

  #[@test, @values([
  #  [new Object()],
  #  [new \ArrayObject([])],
  #])]
  public function object_type_union_newInstance($value) {
    $this->assertInstanceOf(typeof($value), Type::$OBJECT->newInstance($value));
  }

  #[@test]
  public function object_type_union_isAssignableFrom_self() {
    $this->assertTrue(Type::$OBJECT->isAssignableFrom(Type::$OBJECT));
  }

  #[@test]
  public function object_type_union_isAssignableFrom_this_class() {
    $this->assertTrue(Type::$OBJECT->isAssignableFrom(typeof($this)));
  }

  #[@test, @values([Primitive::$INT, Type::$VOID, Type::$VAR, new ArrayType('var'), new MapType('var')])]
  public function object_type_union_is_not_assignable_from($type) {
    $this->assertFalse(Type::$OBJECT->isAssignableFrom($type));
  }
}
