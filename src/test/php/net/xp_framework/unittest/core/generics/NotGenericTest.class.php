<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalStateException;
use unittest\{Assert, Expect, Test};

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