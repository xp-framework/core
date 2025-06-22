<?php namespace lang\unittest;

use lang\{
  ArrayType,
  ClassCastException,
  FunctionType,
  IllegalArgumentException,
  MapType,
  Primitive,
  Type,
  TypeUnion,
  XPClass,
  Nullable
};
use test\verify\Runtime;
use test\{Assert, Expect, Ignore, Test, Values};

class TypeUnionTest {

  /** @return iterable */
  private function instances() {
    yield [1, 'an int'];
    yield ['Test', 'a string'];
  }

  /** @return iterable */
  private function instancesAndNull() {
    yield [null, 'null'];
    yield from $this->instances();
  }

  /** @return iterable */
  private function notInstances() {
    yield [1.0, 'a double'];
    yield [true, 'a boolean'];
    yield [[], 'an array'];
    yield [$this, 'an object'];
  }

  /** @return iterable */
  private function notInstancesAndNull() {
    yield [null, 'null'];
    yield from $this->notInstances();
  }

  /** @return iterable */
  private function isAssignable() {
    yield [Primitive::$INT];
    yield [Primitive::$STRING];
    yield [new XPClass(self::class)];
    yield [new TypeUnion([Primitive::$STRING, Primitive::$INT])];
    yield [new TypeUnion([Primitive::$STRING, Primitive::$INT, new XPClass(self::class)])];
  }

  /** @return iterable */
  private function notAssignable() {
    yield [Type::$VAR];
    yield [Type::$VOID];
    yield [Primitive::$BOOL];
    yield [new TypeUnion([Primitive::$STRING, Primitive::$BOOL])];
    yield [new TypeUnion([Primitive::$STRING, Primitive::$INT, new XPClass(Type::class)])];
    yield [new ArrayType(Type::$VAR)];
    yield [new MapType(Type::$VAR)];
    yield [new FunctionType([], Type::$VAR)];
    yield [new XPClass(Type::class)];
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_create_from_empty() {
    new TypeUnion([]);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_create_from_single() {
    new TypeUnion([Type::$VAR]);
  }

  #[Test]
  public function can_create() {
    new TypeUnion([Primitive::$STRING, Primitive::$INT]);
  }

  #[Test, Values(['string|int', 'string | int', 'int|string'])]
  public function forName($literal) {
    Assert::equals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      TypeUnion::forName($literal)
    );
  }

  #[Test, Values(['string|int', 'string | int', '(string|int)', '(string | int)'])]
  public function forName_from_Type_class($literal) {
    Assert::equals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      Type::forName($literal)
    );
  }

  #[Test]
  public function types() {
    $types= [Primitive::$STRING, Primitive::$INT];
    Assert::equals($types, (new TypeUnion($types))->types());
  }

  #[Test, Values(from: 'instances')]
  public function is_instance_of_a_string_int_union($value) {
    Assert::true((new TypeUnion([Primitive::$STRING, Primitive::$INT]))->isInstance($value));
  }

  #[Test, Values(from: 'notInstancesAndNull')]
  public function is_not_instance_of_a_string_int_union($value) {
    Assert::false((new TypeUnion([Primitive::$STRING, Primitive::$INT]))->isInstance($value));
  }

  #[Test, Values(from: 'instances')]
  public function new_instance_of_a_string_int_union($value) {
    Assert::equals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->newInstance($value)
    );
  }

  #[Test, Expect(IllegalArgumentException::class), Values(from: 'notInstancesAndNull')]
  public function cannot_create_instances_of_a_string_int_union($value) {
    Assert::equals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->newInstance($value)
    );
  }

  #[Test, Values(from: 'instancesAndNull')]
  public function cast_to_a_string_int_union($value) {
    Assert::equals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->cast($value)
    );
  }

  #[Test, Expect(ClassCastException::class), Values(from: 'notInstances')]
  public function cannot_cast_to_a_string_int_union($value) {
    Assert::equals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->cast($value)
    );
  }

  #[Test, Values(from: 'isAssignable')]
  public function is_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, typeof($this)]);
    Assert::true($union->isAssignableFrom($type));
  }

  #[Test, Values(from: 'notAssignable')]
  public function is_not_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, typeof($this)]);
    Assert::false($union->isAssignableFrom($type));
  }

  #[Test]
  public function string_or_int_array() {
    Assert::equals(
      new TypeUnion([Primitive::$STRING, new ArrayType(Primitive::$INT)]),
      Type::forName('string|int[]')
    );
  }

  #[Test]
  public function string_array_or_int() {
    Assert::equals(
      new TypeUnion([new ArrayType(Primitive::$STRING), Primitive::$INT]),
      Type::forName('string[]|int')
    );
  }

  #[Test]
  public function array_of_type_unions() {
    Assert::equals(
      new ArrayType(new TypeUnion([Primitive::$STRING, Primitive::$INT])),
      Type::forName('(string|int)[]')
    );
  }

  #[Test]
  public function literal() {
    Assert::equals(
      "\xb5\xfestring\xb8\xfeint",
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->literal()
    );
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_union_field_type() {
    $f= eval('return new class() { public int|string $fixture; };');
    Assert::equals(
      new TypeUnion([Primitive::$INT, Primitive::$STRING]),
      typeof($f)->getField('fixture')->getType()
    );
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_union_param_type() {
    $f= eval('return new class() { public function fixture(int|string $arg) { } };');
    Assert::equals(
      new TypeUnion([Primitive::$INT, Primitive::$STRING]),
      typeof($f)->getMethod('fixture')->getParameter(0)->getType()
    );
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_union_return_type() {
    $f= eval('return new class() { public function fixture(): int|string { } };');
    Assert::equals(
      new TypeUnion([Primitive::$INT, Primitive::$STRING]),
      typeof($f)->getMethod('fixture')->getReturnType()
    );
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_nullable_union_type() {
    $f= eval('return new class() { public function fixture(int|string|null $arg) { } };');
    Assert::equals(
      new Nullable(new TypeUnion([Primitive::$INT, Primitive::$STRING])),
      typeof($f)->getMethod('fixture')->getParameter(0)->getType()
    );
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_nullable_union_field_type_name() {
    $f= eval('return new class() { public int|string|null $fixture; };');
    Assert::equals('?', typeof($f)->getField('fixture')->getTypeName()[0]);
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_nullable_union_param_type_name() {
    $f= eval('return new class() { public function fixture(int|string|null $arg) { } };');
    Assert::equals('?', typeof($f)->getMethod('fixture')->getParameter(0)->getTypeName()[0]);
  }

  #[Test, Runtime(php: '>=8.0.0-dev')]
  public function php8_native_nullable_union_return_type_name() {
    $f= eval('return new class() { public function fixture(): int|string|null { } };');
    Assert::equals('?', typeof($f)->getMethod('fixture')->getReturnTypeName()[0]);
  }

  #[Test, Runtime(php: '>=8.0.0-dev'), Ignore('https://github.com/php/php-src/issues/18373')]
  public function php8_native_union_with_self() {
    $t= typeof(eval('
      namespace lang\unittest;

      use Countable;

      return new class() {
        public self|Name|Countable|\ArrayObject $fixture;
      };'
    ));
    Assert::equals(
      new TypeUnion([$t, new XPClass(Name::class), new XPClass(\Countable::class), new XPClass(\ArrayObject::class)]),
      $t->getField('fixture')->getType()
    );
  }
}