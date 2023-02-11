<?php namespace net\xp_framework\unittest\core;

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
use net\xp_framework\unittest\Name;
use unittest\actions\RuntimeVersion;
use unittest\{Expect, Test, TestCase, Values, Action};

class TypeUnionTest extends TestCase {

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
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      TypeUnion::forName($literal)
    );
  }

  #[Test, Values(['string|int', 'string | int', '(string|int)', '(string | int)'])]
  public function forName_from_Type_class($literal) {
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      Type::forName($literal)
    );
  }

  #[Test]
  public function types() {
    $types= [Primitive::$STRING, Primitive::$INT];
    $this->assertEquals($types, (new TypeUnion($types))->types());
  }

  #[Test, Values('instances')]
  public function is_instance_of_a_string_int_union($value) {
    $this->assertTrue((new TypeUnion([Primitive::$STRING, Primitive::$INT]))->isInstance($value));
  }

  #[Test, Values('notInstancesAndNull')]
  public function is_not_instance_of_a_string_int_union($value) {
    $this->assertFalse((new TypeUnion([Primitive::$STRING, Primitive::$INT]))->isInstance($value));
  }

  #[Test, Values('instances')]
  public function new_instance_of_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->newInstance($value)
    );
  }

  #[Test, Expect(IllegalArgumentException::class), Values('notInstancesAndNull')]
  public function cannot_create_instances_of_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->newInstance($value)
    );
  }

  #[Test, Values('instancesAndNull')]
  public function cast_to_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->cast($value)
    );
  }

  #[Test, Expect(ClassCastException::class), Values('notInstances')]
  public function cannot_cast_to_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->cast($value)
    );
  }

  #[Test, Values('isAssignable')]
  public function is_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, typeof($this)]);
    $this->assertTrue($union->isAssignableFrom($type));
  }

  #[Test, Values('notAssignable')]
  public function is_not_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, typeof($this)]);
    $this->assertFalse($union->isAssignableFrom($type));
  }

  #[Test]
  public function string_or_int_array() {
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, new ArrayType(Primitive::$INT)]),
      Type::forName('string|int[]')
    );
  }

  #[Test]
  public function string_array_or_int() {
    $this->assertEquals(
      new TypeUnion([new ArrayType(Primitive::$STRING), Primitive::$INT]),
      Type::forName('string[]|int')
    );
  }

  #[Test]
  public function array_of_type_unions() {
    $this->assertEquals(
      new ArrayType(new TypeUnion([Primitive::$STRING, Primitive::$INT])),
      Type::forName('(string|int)[]')
    );
  }

  #[Test]
  public function literal() {
    $this->assertEquals(
      "string\xa6int",
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->literal()
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_union_field_type() {
    $f= eval('return new class() { public int|string $fixture; };');
    $this->assertEquals(
      new TypeUnion([Primitive::$INT, Primitive::$STRING]),
      typeof($f)->getField('fixture')->getType()
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_union_param_type() {
    $f= eval('return new class() { public function fixture(int|string $arg) { } };');
    $this->assertEquals(
      new TypeUnion([Primitive::$INT, Primitive::$STRING]),
      typeof($f)->getMethod('fixture')->getParameter(0)->getType()
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_union_return_type() {
    $f= eval('return new class() { public function fixture(): int|string { } };');
    $this->assertEquals(
      new TypeUnion([Primitive::$INT, Primitive::$STRING]),
      typeof($f)->getMethod('fixture')->getReturnType()
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_nullable_union_type() {
    $f= eval('return new class() { public function fixture(int|string|null $arg) { } };');
    $this->assertEquals(
      new Nullable(new TypeUnion([Primitive::$INT, Primitive::$STRING])),
      typeof($f)->getMethod('fixture')->getParameter(0)->getType()
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_nullable_union_field_type_name() {
    $f= eval('return new class() { public int|string|null $fixture; };');
    $this->assertEquals('?', typeof($f)->getField('fixture')->getTypeName()[0]);
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_nullable_union_param_type_name() {
    $f= eval('return new class() { public function fixture(int|string|null $arg) { } };');
    $this->assertEquals('?', typeof($f)->getMethod('fixture')->getParameter(0)->getTypeName()[0]);
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_nullable_union_return_type_name() {
    $f= eval('return new class() { public function fixture(): int|string|null { } };');
    $this->assertEquals('?', typeof($f)->getMethod('fixture')->getReturnTypeName()[0]);
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_union_with_self() {
    $t= typeof(eval('
      namespace net\xp_framework\unittest;

      use Countable;

      return new class() {
        public self|Name|Countable|\ArrayObject $fixture;
      };'
    ));
    $this->assertEquals(
      new TypeUnion([$t, new XPClass(Name::class), new XPClass(\Countable::class), new XPClass(\ArrayObject::class)]),
      $t->getField('fixture')->getType()
    );
  }
}