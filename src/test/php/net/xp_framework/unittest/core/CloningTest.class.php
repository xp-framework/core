<?php namespace net\xp_framework\unittest\core;

use lang\{CloneNotSupportedException, NullPointerException};
use unittest\{Expect, Test};

class CloningTest extends \unittest\TestCase {

  #[Test]
  public function cloneInterceptorCalled() {
    $original= new class() {
      public $cloned= false;
      public function __clone() { $this->cloned= true; }
    };
    $this->assertFalse($original->cloned);
    $clone= clone($original);
    $this->assertFalse($original->cloned);
    $this->assertTrue($clone->cloned);
  }

  #[Test, Expect(CloneNotSupportedException::class)]
  public function cloneInterceptorThrowsException() {
    clone(new class() {
      public function __clone() { throw new CloneNotSupportedException('I am *UN*Cloneable'); }
    });
  }
}