<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalStateException;

/**
 * TestCase for reflection on a non-generic
 */
class NotGenericTest extends \unittest\TestCase {
  
  #[@test]
  public function thisIsNotAGeneric() {
    $this->assertFalse($this->getClass()->isGeneric());
  }

  #[@test]
  public function thisIsNotAGenericDefinition() {
    $this->assertFalse($this->getClass()->isGenericDefinition());
  }

  #[@test, @expect(IllegalStateException::class)]
  public function cannotCreateGenericTypeFromThis() {
    $this->getClass()->newGenericType([]);
  }

  #[@test, @expect(IllegalStateException::class)]
  public function cannotGetGenericArgumentsForThis() {
    $this->getClass()->genericArguments();
  }

  #[@test, @expect(IllegalStateException::class)]
  public function cannotGetGenericComponentsForThis() {
    $this->getClass()->genericComponents();
  }
}
