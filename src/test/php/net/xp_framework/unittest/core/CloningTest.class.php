<?php namespace net\xp_framework\unittest\core;

use lang\{CloneNotSupportedException, NullPointerException};
use unittest\Assert;
use unittest\{Expect, Test};

class CloningTest {

  #[Test]
  public function cloneInterceptorCalled() {
    $original= new class() {
      public $cloned= false;
      public function __clone() { $this->cloned= true; }
    };
    Assert::false($original->cloned);
    $clone= clone($original);
    Assert::false($original->cloned);
    Assert::true($clone->cloned);
  }

  #[Test, Expect(CloneNotSupportedException::class)]
  public function cloneInterceptorThrowsException() {
    clone(new class() {
      public function __clone() { throw new CloneNotSupportedException('I am *UN*Cloneable'); }
    });
  }
}