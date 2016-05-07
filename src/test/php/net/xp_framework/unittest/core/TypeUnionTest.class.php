<?php namespace net\xp_framework\unittest\core;

use lang\TypeUnion;
use lang\IllegalArgumentException;
use lang\ClassCastException;
use lang\Primitive;
use lang\ArrayType;
use lang\MapType;
use lang\FunctionType;
use lang\Type;
use lang\XPClass;

class TypeUnionTest extends \unittest\TestCase {

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

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_create_from_empty() {
    new TypeUnion([]);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_create_from_single() {
    new TypeUnion([Type::$VAR]);
  }

  #[@test]
  public function can_create() {
    new TypeUnion([Primitive::$STRING, Primitive::$INT]);
  }

  #[@test, @values([
  #  'string|int',
  #  'string | int'
  #])]
  public function forName($literal) {
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      TypeUnion::forName($literal)
    );
  }

  #[@test, @values([
  #  'string|int',
  #  'string | int',
  #  '(string|int)',
  #  '(string | int)'
  #])]
  public function forName_from_Type_class($literal) {
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, Primitive::$INT]),
      Type::forName($literal)
    );
  }

  #[@test]
  public function types() {
    $types= [Primitive::$STRING, Primitive::$INT];
    $this->assertEquals($types, (new TypeUnion($types))->types());
  }

  #[@test, @values('instances')]
  public function is_instance_of_a_string_int_union($value) {
    $this->assertTrue((new TypeUnion([Primitive::$STRING, Primitive::$INT]))->isInstance($value));
  }

  #[@test, @values('notInstancesAndNull')]
  public function is_not_instance_of_a_string_int_union($value) {
    $this->assertFalse((new TypeUnion([Primitive::$STRING, Primitive::$INT]))->isInstance($value));
  }

  #[@test, @values('instances')]
  public function new_instance_of_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->newInstance($value)
    );
  }

  #[@test, @expect(IllegalArgumentException::class), @values('notInstancesAndNull')]
  public function cannot_create_instances_of_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->newInstance($value)
    );
  }

  #[@test, @values('instancesAndNull')]
  public function cast_to_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->cast($value)
    );
  }

  #[@test, @expect(ClassCastException::class), @values('notInstances')]
  public function cannot_cast_to_a_string_int_union($value) {
    $this->assertEquals(
      $value,
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->cast($value)
    );
  }

  #[@test, @values([
  #  [Primitive::$INT],
  #  [Primitive::$STRING],
  #  [new XPClass(self::class)],
  #  [new TypeUnion([Primitive::$STRING, Primitive::$INT])],
  #  [new TypeUnion([Primitive::$STRING, Primitive::$INT, new XPClass(self::class)])]
  #])]
  public function is_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, $this->getClass()]);
    $this->assertTrue($union->isAssignableFrom($type));
  }

  #[@test, @values([
  #  [Type::$VAR],
  #  [Type::$VOID],
  #  [Primitive::$BOOL],
  #  [new TypeUnion([Primitive::$STRING, Primitive::$BOOL])],
  #  [new TypeUnion([Primitive::$STRING, Primitive::$INT, new XPClass(Type::class)])],
  #  [new ArrayType(Type::$VAR)],
  #  [new MapType(Type::$VAR)],
  #  [new FunctionType([], Type::$VAR)],
  #  [new XPClass(Type::class)]
  #])]
  public function is_not_assignable_from($type) {
    $union= new TypeUnion([Primitive::$STRING, Primitive::$INT, $this->getClass()]);
    $this->assertFalse($union->isAssignableFrom($type));
  }

  #[@test]
  public function string_or_int_array() {
    $this->assertEquals(
      new TypeUnion([Primitive::$STRING, new ArrayType(Primitive::$INT)]),
      Type::forName('string|int[]')
    );
  }

  #[@test]
  public function array_of_type_unions() {
    $this->assertEquals(
      new ArrayType(new TypeUnion([Primitive::$STRING, Primitive::$INT])),
      Type::forName('(string|int)[]')
    );
  }

  #[@test]
  public function literal() {
    $this->assertEquals(
      "\xb5\xfestring\xb8\xfeint",
      (new TypeUnion([Primitive::$STRING, Primitive::$INT]))->literal()
    );
  }
}