<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalStateException;

/**
 * TestCase for reflection on a non-generic
 */
class NotGenericTest extends \unittest\TestCase {
  
  #[@test]
  public function thisIsNotAGeneric() {
    $this->assertFalse(typeof($this)->isGeneric());
  }

  #[@test]
  public function thisIsNotAGenericDefinition() {
    $this->assertFalse(typeof($this)->isGenericDefinition());
  }

  #[@test, @expect(IllegalStateException::class)]
  public function cannotCreateGenericTypeFromThis() {
    typeof($this)->newGenericType([]);
  }

  #[@test, @expect(IllegalStateException::class)]
  public function cannotGetGenericArgumentsForThis() {
    typeof($this)->genericArguments();
  }

  #[@test, @expect(IllegalStateException::class)]
  public function cannotGetGenericComponentsForThis() {
    typeof($this)->genericComponents();
  }
}
