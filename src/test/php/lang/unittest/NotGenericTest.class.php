<?php namespace lang\unittest;

use lang\IllegalStateException;
use test\{Assert, Expect, Test};

class NotGenericTest {
  
  #[Test]
  public function thisIsNotAGeneric() {
    Assert::false(typeof($this)->isGeneric());
  }

  #[Test]
  public function thisIsNotAGenericDefinition() {
    Assert::false(typeof($this)->isGenericDefinition());
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotCreateGenericTypeFromThis() {
    typeof($this)->newGenericType([]);
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotGetGenericArgumentsForThis() {
    typeof($this)->genericArguments();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function cannotGetGenericComponentsForThis() {
    typeof($this)->genericComponents();
  }
}