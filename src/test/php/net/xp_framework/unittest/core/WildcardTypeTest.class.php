<?php namespace net\xp_framework\unittest\core;

use lang\WildcardType;
use lang\Wildcard;
use lang\XPClass;
use lang\Primitive;
use lang\Type;

/**
 * TestCase
 *
 * @see      xp://lang.WildcardType
 */
class WildcardTypeTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new WildcardType(XPClass::forName('util.collections.Vector'), [Wildcard::$ANY]);
  }

  #[@test]
  public function name_accessor_produces_string_form() {
    $this->assertEquals(
      'util.collections.Vector<?>',
      (new WildcardType(XPClass::forName('util.collections.Vector'), [Wildcard::$ANY]))->getName()
    );
  }

  #[@test]
  public function base_accessor_returns_base() {
    $base= XPClass::forName('util.collections.Vector');
    $components= [Wildcard::$ANY];
    $this->assertEquals($base, (new WildcardType($base, $components))->base());
  }

  #[@test]
  public function components_accessor_returns_components() {
    $base= XPClass::forName('util.collections.Vector');
    $components= [Wildcard::$ANY];
    $this->assertEquals($components, (new WildcardType($base, $components))->components());
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([
  #  'util.collections.Vector',
  #  'int',
  #  'string[]',
  #  '[:lang.Generic]',
  #  'util.collections.Vector<bool>',
  #  '?', '??', 'string?'
  #])]
  public function forName_raises_exception_for_non_wildcard_types($type) {
    WildcardType::forName($type);
  }

  #[@test]
  public function forName_parsed_base_type_with_one_wildcard() {
    $this->assertEquals(
      XPClass::forName('util.collections.Vector'),
      WildcardType::forName('util.collections.Vector<?>')->base()
    );
  }

  #[@test]
  public function forName_parsed_base_type_with_two_wildcards() {
    $this->assertEquals(
      XPClass::forName('util.collections.HashTable'),
      WildcardType::forName('util.collections.HashTable<?, ?>')->base()
    );
  }

  #[@test]
  public function forName_parsed_components_with_one_wildcard() {
    $this->assertEquals(
      [Wildcard::$ANY],
      WildcardType::forName('util.collections.Vector<?>')->components()
    );
  }

  #[@test]
  public function forName_parsed_components_with_two_wildcards() {
    $this->assertEquals(
      [Wildcard::$ANY, Wildcard::$ANY],
      WildcardType::forName('util.collections.HashTable<?, ?>')->components()
    );
  }

  #[@test]
  public function forName_parsed_components_with_one_bound_and_one_wildcard() {
    $this->assertEquals(
      [Primitive::$STRING, Wildcard::$ANY],
      WildcardType::forName('util.collections.HashTable<string, ?>')->components()
    );
  }

  #[@test]
  public function forName_parsed_nested_wildcard_type() {
    $this->assertEquals(
      [new WildcardType(XPClass::forName('util.collections.HashTable'), [Wildcard::$ANY, Wildcard::$ANY])],
      WildcardType::forName('util.collections.Vector<util.collections.HashTable<?, ?>>')->components()
    );
  }

  #[@test]
  public function forName_parsed_deeply_nested_wildcard_type() {
    $this->assertEquals(
      [new WildcardType(XPClass::forName('util.collections.Vector'), [
        new WildcardType(XPClass::forName('util.collections.Vector'), [
          Wildcard::$ANY
        ])
      ])],
      WildcardType::forName('util.collections.Vector<util.collections.Vector<util.collections.Vector<?>>>')->components()
    );
  }

  /** @return var[][] */
  protected function vectorOfAny() {
    return [
      [Type::forName('util.collections.Vector<string>')],
      [Type::forName('util.collections.Vector<lang.Generic>')],
      [Type::forName('util.collections.Vector<util.collections.Vector<int>>')],
    ];
  }

  /** @return var[][] */
  protected function hashTableOfAny() {
    return [
      [Type::forName('util.collections.HashTable<int, string>')],
      [Type::forName('util.collections.HashTable<string, lang.Generic>')],
      [Type::forName('util.collections.HashTable<lang.Generic, util.collections.Vector<int>>')],
    ];
  }

  /** @return var[][] */
  protected function unGenericInstances() {
    return [
      [[0], [-1], [6.1], [true], [false], [''], ['Test']],
      [[], [1, 2, 3]], ['key' => 'value'],
      $this
    ];
  }

  #[@test, @values('vectorOfAny')]
  public function generic_vectors_are_instances_of_vector_of_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.Vector<?>')->isInstance($value->newInstance()));
  }

  #[@test, @values('vectorOfAny')]
  public function generic_vectors_are_assignable_to_vector_of_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.Vector<?>')->isAssignableFrom($value));
  }

  #[@test, @values('vectorOfAny')]
  public function generic_vectors_are_instances_of_ilist_of_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.IList<?>')->isInstance($value->newInstance()));
  }

  #[@test, @values('vectorOfAny')]
  public function generic_vectors_are_assignable_to_ilist_of_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.IList<?>')->isAssignableFrom($value));
  }

  #[@test, @values('hashTableOfAny')]
  public function generic_hashtables_are_not_instances_of_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('util.collections.Vector<?>')->isInstance($value->newInstance()));
  }

  #[@test, @values('hashTableOfAny')]
  public function generic_hashtables_are_not_assignable_to_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('util.collections.Vector<?>')->isAssignableFrom($value));
  }

  #[@test, @values('unGenericInstances')]
  public function ungeneric_instances_are_not_instances_of_vector_of_any($value) {
    $this->assertFalse(WildcardType::forName('util.collections.Vector<?>')->isInstance($value));
  }

  #[@test, @values('hashTableOfAny')]
  public function generic_hashtables_are_instances_of_hash_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.HashTable<?, ?>')->isInstance($value->newInstance()));
  }

  #[@test, @values('hashTableOfAny')]
  public function generic_hashtables_are_assignable_to_of_hash_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.HashTable<?, ?>')->isAssignableFrom($value));
  }

  #[@test, @values('hashTableOfAny')]
  public function generic_hashtables_are_instances_of_map_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.Map<?, ?>')->isInstance($value->newInstance()));
  }

  #[@test, @values('hashTableOfAny')]
  public function generic_hashtables_are_assignable_to_of_map_of_any_any($value) {
    $this->assertTrue(WildcardType::forName('util.collections.Map<?, ?>')->isAssignableFrom($value));
  }

  #[@test, @values('vectorOfAny')]
  public function generic_vectors_are_not_instances_of_hash_of_any_any($value) {
    $this->assertFalse(WildcardType::forName('util.collections.HashTable<?, ?>')->isInstance($value->newInstance()));
  }

  #[@test, @values('vectorOfAny')]
  public function generic_vectors_are_not_assignable_to_hash_of_any_any($value) {
    $this->assertFalse(WildcardType::forName('util.collections.HashTable<?, ?>')->isAssignableFrom($value));
  }

  #[@test, @values('unGenericInstances')]
  public function ungeneric_instances_are_not_instances_of_hash_of_any_any($value) {
    $this->assertFalse(WildcardType::forName('util.collections.HashTable<?, ?>')->isInstance($value));
  }

  #[@test]
  public function hash_table_of_string_Generic_is_not_instance_of_hash_of_int_any() {
    $this->assertFalse(WildcardType::forName('util.collections.HashTable<int, ?>')->isInstance(
      create('new util.collections.HashTable<string, lang.Generic>')
    ));
  }

  #[@test]
  public function hash_table_of_string_Generic_is_assignable_top_hash_of_int_any() {
    $this->assertFalse(WildcardType::forName('util.collections.HashTable<int, ?>')->isAssignableFrom(
      Type::forName('util.collections.HashTable<string, lang.Generic>')
    ));
  }
}
