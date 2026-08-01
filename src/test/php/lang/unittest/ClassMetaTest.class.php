<?php namespace lang\unittest;

use lang\ClassMeta;
use lang\unittest\Name as Named;
use lang\unittest\fixture\{ClassOne, InterfaceOne, TraitOne};
use test\{Assert, Test, Values};

class ClassMetaTest {

  #[Test]
  public function can_create() {
    new ClassMeta();
  }

  #[Test, Values([ClassOne::class, InterfaceOne::class, TraitOne::class])]
  public function empty_type_named($class) {
    Assert::equals(
      [DETAIL_COMMENT => ''],
      (new ClassMeta())->meta($class)['class']
    );
  }

  #[Test]
  public function property_comment() {
    $meta= (new ClassMeta())->meta(new class() {

      /** Test */
      private $test;
    });

    Assert::equals('Test', $meta[0]['test'][DETAIL_COMMENT]);
  }

  #[Test]
  public function property_type() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @type function(): int */
      private $test;
    });

    Assert::equals('function(): int', $meta[0]['test'][DETAIL_RETURNS]);
  }

  #[Test]
  public function method_comment() {
    $meta= (new ClassMeta())->meta(new class() {

      /**
       * Test
       * 
       * @see https://example.com/
       */
      public function test() { }
    });

    Assert::equals('Test', $meta[1]['test'][DETAIL_COMMENT]);
  }

  #[Test]
  public function compact_doc_comment() {
    $meta= (new ClassMeta())->meta(new class() {

      /** Test */
      public function test($in) { }
    });

    Assert::equals('Test', $meta[1]['test'][DETAIL_COMMENT]);
  }

  #[Test]
  public function param_tag() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @param string[] $in */
      public function test($in) { }
    });

    Assert::equals('string[]', $meta[1]['test'][DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function return_tag() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @return string */
      public function test() { }
    });

    Assert::equals('string', $meta[1]['test'][DETAIL_RETURNS]);
  }

  #[Test]
  public function throws_tag() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @throws lang.IllegalArgumentException */
      public function test($in) { }
    });

    Assert::equals('lang.IllegalArgumentException', $meta[1]['test'][DETAIL_THROWS][0]);
  }

  #[Test]
  public function generic_type() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @return util.collection.HashTable<string, util.Filter<string>> */
      public function test() { }
    });

    Assert::equals('util.collection.HashTable<string, util.Filter<string>>', $meta[1]['test'][DETAIL_RETURNS]);
  }

  #[Test]
  public function unqualified_type() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @return Name */
      public function test() { }
    });

    Assert::equals('lang.unittest.Name', $meta[1]['test'][DETAIL_RETURNS]);
  }

  #[Test]
  public function imported_type() {
    $meta= (new ClassMeta())->meta(new class() {

      /** @return Named */
      public function test() { }
    });

    Assert::equals('lang.unittest.Name', $meta[1]['test'][DETAIL_RETURNS]);
  }
}