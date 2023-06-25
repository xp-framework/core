<?php namespace lang\unittest;

use lang\{ClassCastException, IllegalArgumentException, Nullable, Type};
use test\{Assert, Expect, Test, Values};

class NullableTest {

  /** @return iterable */
  private function instances() {
    yield ['', true];
    yield ['Test', true];
    yield [null, true];
    yield [1, false];
    yield [1.5, false];
  }

  /** @return iterable */
  private function castables() {
    yield ['', ''];
    yield ['Test', 'Test'];
    yield [null, null];
    yield [1, '1'];
    yield [1.5, '1.5'];
  }

  #[Test]
  public function type_factory() {
    Assert::instance(Nullable::class, Type::forName('?string'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function non_nullable_type_passed_to_factory() {
    Nullable::forName('string');
  }

  #[Test, Values(from: 'instances')]
  public function is_instance($value, $expected) {
    Assert::equals($expected, Type::forName('?string')->isInstance($value));
  }

  #[Test, Values(from: 'castables')]
  public function cast($value, $expected) {
    Assert::equals($expected, Type::forName('?string')->cast($value));
  }

  #[Test, Expect(ClassCastException::class)]
  public function cannot_cast_objects() {
    Type::forName('?string')->cast(new Name('Test'));
  }

  #[Test, Expect(ClassCastException::class)]
  public function cannot_cast_arrays() {
    Type::forName('?string')->cast([1, 2, 3]);
  }
}