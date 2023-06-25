<?php namespace lang\unittest;

use lang\IllegalArgumentException;
use test\{Assert, Expect, Test};

class VarArgsTest {

  #[Test]
  public function withArguments() {
    Assert::equals(
      ['Hello', 'World'],
      create('new lang.unittest.ListOf<string>', 'Hello', 'World')->elements()
    );
  }

  #[Test]
  public function withoutArguments() {
    Assert::equals(
      [],
      create('new lang.unittest.ListOf<string>')->elements()
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function withIncorrectArguments() {
    create('new lang.unittest.ListOf<string>', 'Hello', 1);
  }

  #[Test]
  public function withAllOf() {
    Assert::equals(
      ['Hello', 'World'],
      create('new lang.unittest.ListOf<string>')->withAll('Hello', 'World')->elements()
    );
  }
}