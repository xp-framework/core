<?php namespace net\xp_framework\unittest\core;

use lang\{
  ArrayType,
  ClassCastException,
  IllegalAccessException,
  IllegalArgumentException,
  MapType,
  Primitive,
  Type,
  Wildcard,
  WildcardType,
  XPClass
};
use unittest\{Expect, Test, Values, TestCase};

class WildcardTypeTest extends TestCase {

  #[Test]
  public function can_create() {
    new WildcardType(XPClass::forName('net.xp_framework.unittest.core.generics.Nullable'), [Wildcard::$ANY]);
  }

  #[Test]
  public function name_accessor_produces_string_form() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Nullable<?>',
      (new WildcardType(XPClass::forName('net.xp_framework.unittest.core.generics.Nullable'), [Wildcard::$ANY]))->getName()
    );
  }

  #[Test]
  public function base_accessor_returns_base() {
    $base= XPClass::forName('net.xp_framework.unittest.core.generics.Nullable');
    $components= [Wildcard::$ANY];
    $this->assertEquals($base, (new WildcardType($base, $components))->base());
  }

  #[Test]
  public function components_accessor_returns_components() {
    $base= XPClass::forName('net.xp_framework.unittest.core.generics.Nullable');
    $components= [Wildcard::$ANY];
    $this->assertEquals($components, (new WildcardType($base, $components))->components());
  }

  #[Test, Expect(IllegalArgumentException::class), Values(['net.xp_framework.unittest.core.generics.Nullable', 'int', 'string[]', '[:lang.Value]', 'net.xp_framework.unittest.core.generics.Nullable<bool>', '?', '??', 'string?'])]
  public function forName_raises_exception_for_non_wildcard_types($type) {
    WildcardType::forName($type);
  }

  #[Test]
  public function forName_parsed_base_type_with_one_wildcard() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Nullable'),
      WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->base()
    );
  }

  #[Test]
  public function forName_parsed_base_type_with_two_wildcards() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Lookup'),
      WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->base()
    );
  }

  #[Test]
  public function forName_parsed_components_with_one_wildcard() {
    $this->assertEquals(
      [Wildcard::$ANY],
      WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->components()
    );
  }

  #[Test]
  public function forName_parsed_components_with_two_wildcards() {
    $this->assertEquals(
      [Wildcard::$ANY, Wildcard::$ANY],
      WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->components()
    );
  }

  #[Test]
  public function forName_parsed_components_with_one_bound_and_one_wildcard() {
    $this->assertEquals(
      [Primitive::$STRING, Wildcard::$ANY],
      WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<string, ?>')->components()
    );
  }

  #[Test]
  public function forName_parsed_nested_wildcard_type() {
    $this->assertEquals(
      [new WildcardType(XPClass::forName('net.xp_framework.unittest.core.generics.Lookup'), [Wildcard::$ANY, Wildcard::$ANY])],
      WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.generics.Lookup<?, ?>>')->components()
    );
  }

  #[Test]
  public function forName_parsed_deeply_nested_wildcard_type() {
    $this->assertEquals(
      [new WildcardType(XPClass::forName('net.xp_framework.unittest.core.generics.Nullable'), [
        new WildcardType(XPClass::forName('net.xp_framework.unittest.core.generics.Nullable'), [
          Wildcard::$ANY
        ])
      ])],
      WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.generics.Nullable<?>>>')->components()
    );
  }

  /** @return var[][] */
  protected function nullableOfAny() {
    return [
      [Type::forName('net.xp_framework.unittest.core.generics.Nullable<string>')],
      [Type::forName('net.xp_framework.unittest.core.generics.Nullable<lang.Value>')],
      [Type::forName('net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.generics.Nullable<int>>')],
    ];
  }

  /** @return var[][] */
  protected function hashTableOfAny() {
    return [
      [Type::forName('net.xp_framework.unittest.core.generics.Lookup<int, string>')],
      [Type::forName('net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>')],
      [Type::forName('net.xp_framework.unittest.core.generics.Lookup<lang.Value, net.xp_framework.unittest.core.generics.Nullable<int>>')],
    ];
  }

  /** @return var[][] */
  protected function unGenericTypes() {
    return [
      [Primitive::$INT], [Primitive::$FLOAT], [Primitive::$BOOL], [Primitive::$STRING],
      [new ArrayType('var'), new MapType('var')],
      [typeof($this)]
    ];
  }

  /** @return var[][] */
  protected function unGenericInstances() {
    return [
      [[0], [-1], [6.1], [true], [false], [''], ['Test']],
      [[], [1, 2, 3]], [['key' => 'value']],
      $this
    ];
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_instances_of_vector_of_any($value) {
    $this->assertTrue(WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_assignable_to_vector_of_any($value) {
    $this->assertTrue(WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->isAssignableFrom($value));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_can_be_cast_to_vector_of_any($value) {
    $instance= $value->newInstance();
    $this->assertEquals($instance, WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->cast($instance));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_not_instances_of_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_not_assignable_to_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->isAssignableFrom($value));
  }

  #[Test, Expect(ClassCastException::class), Values('hashTableOfAny')]
  public function generic_hashtables_cannot_be_cast_to_vector_of_any($value) {
    WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->cast($value->newInstance());
  }

  #[Test, Values('unGenericInstances')]
  public function ungeneric_instances_are_not_instances_of_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->isInstance($value));
  }

  #[Test, Values('unGenericTypes')]
  public function ungeneric_instances_are_not_assignable_to_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->isAssignableFrom($value));
  }

  #[Test, Expect(ClassCastException::class), Values('unGenericInstances')]
  public function ungeneric_instancess_cannot_be_cast_to_vector_of_any($value) {
    WildcardType::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->cast($value);
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_instances_of_hash_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_assignable_to_of_hash_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->isAssignableFrom($value));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_instances_of_map_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('net.xp_framework.unittest.core.generics.IDictionary<?, ?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_assignable_to_of_map_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('net.xp_framework.unittest.core.generics.IDictionary<?, ?>')->isAssignableFrom($value));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_not_instances_of_hash_of_any_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_not_assignable_to_hash_of_any_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->isAssignableFrom($value));
  }

  #[Test, Values('unGenericInstances')]
  public function ungeneric_instances_are_not_instances_of_hash_of_any_any($value) {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<?, ?>')->isInstance($value));
  }

  #[Test]
  public function hash_table_of_string_Value_is_not_instance_of_hash_of_int_any() {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<int, ?>')->isInstance(
      create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function hash_table_of_string_Value_is_assignable_top_hash_of_int_any() {
    $this->assertFalse(WildcardType::forName('net.xp_framework.unittest.core.generics.Lookup<int, ?>')->isAssignableFrom(
      Type::forName('net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function lang_Type_forName_parsed_base_type_with_one_wildcard() {
    $this->assertEquals(
      new WildcardType(XPClass::forName('net.xp_framework.unittest.core.generics.Nullable'), [Wildcard::$ANY]),
      Type::forName('net.xp_framework.unittest.core.generics.Nullable<?>')
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function wildcard_types_cannot_be_instantiated() {
    Type::forName('net.xp_framework.unittest.core.generics.Nullable<?>')->newInstance();
  }
}