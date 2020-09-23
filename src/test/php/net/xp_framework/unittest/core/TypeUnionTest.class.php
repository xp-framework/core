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
  XPClass
};
use unittest\{Expect, Test, TestCase, Values};

class TypeUnionTest extends TestCase {

  /** @return var[][] */
  private function instances() {
    return [
      [1, 'an int'],
      ['Test', 'a string']
    ];
  }

  /** @return var[][] */
  private function instancesAndNull() {
    return array_merge([[null, 'null']], $this->instances());
  }

  /** @return var[][] */
  private function notInstances() {
    return [
      [1.0, 'a double'],
      [true, 'a boolean'],
      [[], 'an array'],
      [$this, 'an object']
    ];
  }

  /** @return var[][] */
  private function notInstancesAndNull() {
    return array_merge([[null, 'null']], $this->notInstances());
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

  #[Test, Values(['string|int', 'string | int'])]
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

  #[Test, Values([[Primitive::$INT], [Primitive::$STRING], [new XPClass(self::class)], [new TypeUnion([Primitive::$STRING, Primitive::$INT])], [new TypeUnion([Primitive::$STRING, Primitive::$INT, new XPClass(self::class)])]])]
  public function is_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, typeof($this)]);
    $this->assertTrue($union->isAssignableFrom($type));
  }

  #[Test, Values([[Type::$VAR], [Type::$VOID], [Primitive::$BOOL], [new TypeUnion([Primitive::$STRING, Primitive::$BOOL])], [new TypeUnion([Primitive::$STRING, Primitive::$INT, new XPClass(Type::class)])], [new ArrayType(Type::$VAR)], [new MapType(Type::$VAR)], [new FunctionType([], Type::$VAR)], [new XPClass(Type::class)]])]
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
      "\xb5\xfestring\xb8\xfeint",
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->literal()
    );
  }
}