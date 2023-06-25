<?php namespace lang\unittest;

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
use unittest\{Assert, Expect, Test, Values};

class WildcardTypeTest {

  #[Test]
  public function can_create() {
    new WildcardType(XPClass::forName('lang.unittest.Nullable'), [Wildcard::$ANY]);
  }

  #[Test]
  public function name_accessor_produces_string_form() {
    Assert::equals(
      'lang.unittest.Nullable<?>',
      (new WildcardType(XPClass::forName('lang.unittest.Nullable'), [Wildcard::$ANY]))->getName()
    );
  }

  #[Test]
  public function base_accessor_returns_base() {
    $base= XPClass::forName('lang.unittest.Nullable');
    $components= [Wildcard::$ANY];
    Assert::equals($base, (new WildcardType($base, $components))->base());
  }

  #[Test]
  public function components_accessor_returns_components() {
    $base= XPClass::forName('lang.unittest.Nullable');
    $components= [Wildcard::$ANY];
    Assert::equals($components, (new WildcardType($base, $components))->components());
  }

  #[Test, Expect(IllegalArgumentException::class), Values(['lang.unittest.Nullable', 'int', 'string[]', '[:lang.Value]', 'lang.unittest.Nullable<bool>', '?', '??', 'string?'])]
  public function forName_raises_exception_for_non_wildcard_types($type) {
    WildcardType::forName($type);
  }

  #[Test]
  public function forName_parsed_base_type_with_one_wildcard() {
    Assert::equals(
      XPClass::forName('lang.unittest.Nullable'),
      WildcardType::forName('lang.unittest.Nullable<?>')->base()
    );
  }

  #[Test]
  public function forName_parsed_base_type_with_two_wildcards() {
    Assert::equals(
      XPClass::forName('lang.unittest.Lookup'),
      WildcardType::forName('lang.unittest.Lookup<?, ?>')->base()
    );
  }

  #[Test]
  public function forName_parsed_components_with_one_wildcard() {
    Assert::equals(
      [Wildcard::$ANY],
      WildcardType::forName('lang.unittest.Nullable<?>')->components()
    );
  }

  #[Test]
  public function forName_parsed_components_with_two_wildcards() {
    Assert::equals(
      [Wildcard::$ANY, Wildcard::$ANY],
      WildcardType::forName('lang.unittest.Lookup<?, ?>')->components()
    );
  }

  #[Test]
  public function forName_parsed_components_with_one_bound_and_one_wildcard() {
    Assert::equals(
      [Primitive::$STRING, Wildcard::$ANY],
      WildcardType::forName('lang.unittest.Lookup<string, ?>')->components()
    );
  }

  #[Test]
  public function forName_parsed_nested_wildcard_type() {
    Assert::equals(
      [new WildcardType(XPClass::forName('lang.unittest.Lookup'), [Wildcard::$ANY, Wildcard::$ANY])],
      WildcardType::forName('lang.unittest.Nullable<lang.unittest.Lookup<?, ?>>')->components()
    );
  }

  #[Test]
  public function forName_parsed_deeply_nested_wildcard_type() {
    Assert::equals(
      [new WildcardType(XPClass::forName('lang.unittest.Nullable'), [
        new WildcardType(XPClass::forName('lang.unittest.Nullable'), [
          Wildcard::$ANY
        ])
      ])],
      WildcardType::forName('lang.unittest.Nullable<lang.unittest.Nullable<lang.unittest.Nullable<?>>>')->components()
    );
  }

  /** @return var[][] */
  protected function nullableOfAny() {
    return [
      [Type::forName('lang.unittest.Nullable<string>')],
      [Type::forName('lang.unittest.Nullable<lang.Value>')],
      [Type::forName('lang.unittest.Nullable<lang.unittest.Nullable<int>>')],
    ];
  }

  /** @return var[][] */
  protected function hashTableOfAny() {
    return [
      [Type::forName('lang.unittest.Lookup<int, string>')],
      [Type::forName('lang.unittest.Lookup<string, lang.Value>')],
      [Type::forName('lang.unittest.Lookup<lang.Value, lang.unittest.Nullable<int>>')],
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
    Assert::true(WildcardType::forName('lang.unittest.Nullable<?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_assignable_to_vector_of_any($value) {
    Assert::true(WildcardType::forName('lang.unittest.Nullable<?>')->isAssignableFrom($value));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_can_be_cast_to_vector_of_any($value) {
    $instance= $value->newInstance();
    Assert::equals($instance, WildcardType::forName('lang.unittest.Nullable<?>')->cast($instance));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_not_instances_of_vector_of_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Nullable<?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_not_assignable_to_vector_of_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Nullable<?>')->isAssignableFrom($value));
  }

  #[Test, Expect(ClassCastException::class), Values('hashTableOfAny')]
  public function generic_hashtables_cannot_be_cast_to_vector_of_any($value) {
    WildcardType::forName('lang.unittest.Nullable<?>')->cast($value->newInstance());
  }

  #[Test, Values('unGenericInstances')]
  public function ungeneric_instances_are_not_instances_of_vector_of_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Nullable<?>')->isInstance($value));
  }

  #[Test, Values('unGenericTypes')]
  public function ungeneric_instances_are_not_assignable_to_vector_of_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Nullable<?>')->isAssignableFrom($value));
  }

  #[Test, Expect(ClassCastException::class), Values('unGenericInstances')]
  public function ungeneric_instancess_cannot_be_cast_to_vector_of_any($value) {
    WildcardType::forName('lang.unittest.Nullable<?>')->cast($value);
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_instances_of_hash_of_any_any($value) {
    Assert::true(WildcardType::forName('lang.unittest.Lookup<?, ?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_assignable_to_of_hash_of_any_any($value) {
    Assert::true(WildcardType::forName('lang.unittest.Lookup<?, ?>')->isAssignableFrom($value));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_instances_of_map_of_any_any($value) {
    Assert::true(WildcardType::forName('lang.unittest.IDictionary<?, ?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('hashTableOfAny')]
  public function generic_hashtables_are_assignable_to_of_map_of_any_any($value) {
    Assert::true(WildcardType::forName('lang.unittest.IDictionary<?, ?>')->isAssignableFrom($value));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_not_instances_of_hash_of_any_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Lookup<?, ?>')->isInstance($value->newInstance()));
  }

  #[Test, Values('nullableOfAny')]
  public function generic_vectors_are_not_assignable_to_hash_of_any_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Lookup<?, ?>')->isAssignableFrom($value));
  }

  #[Test, Values('unGenericInstances')]
  public function ungeneric_instances_are_not_instances_of_hash_of_any_any($value) {
    Assert::false(WildcardType::forName('lang.unittest.Lookup<?, ?>')->isInstance($value));
  }

  #[Test]
  public function hash_table_of_string_Value_is_not_instance_of_hash_of_int_any() {
    Assert::false(WildcardType::forName('lang.unittest.Lookup<int, ?>')->isInstance(
      create('new lang.unittest.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function hash_table_of_string_Value_is_assignable_top_hash_of_int_any() {
    Assert::false(WildcardType::forName('lang.unittest.Lookup<int, ?>')->isAssignableFrom(
      Type::forName('lang.unittest.Lookup<string, lang.Value>')
    ));
  }

  #[Test]
  public function lang_Type_forName_parsed_base_type_with_one_wildcard() {
    Assert::equals(
      new WildcardType(XPClass::forName('lang.unittest.Nullable'), [Wildcard::$ANY]),
      Type::forName('lang.unittest.Nullable<?>')
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function wildcard_types_cannot_be_instantiated() {
    Type::forName('lang.unittest.Nullable<?>')->newInstance();
  }
}