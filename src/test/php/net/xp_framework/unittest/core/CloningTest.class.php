<?php namespace net\xp_framework\unittest\core;

use lang\Object;
use lang\NullPointerException;
use lang\CloneNotSupportedException;

class CloningTest extends \unittest\TestCase {

  #[@test]
  public function cloneOfObject() {
    $original= new Object();
    $this->assertFalse($original == clone($original));
  }

  #[@test]
  public function cloneInterceptorCalled() {
    $original= newinstance(Object::class, [], '{
      public $cloned= false;
      public function __clone() { $this->cloned= true; }
    }');
    $this->assertFalse($original->cloned);
    $clone= clone($original);
    $this->assertFalse($original->cloned);
    $this->assertTrue($clone->cloned);
  }

  #[@test, @expect(CloneNotSupportedException::class)]
  public function cloneInterceptorThrowsException() {
    clone(newinstance(Object::class, [], [
      '__clone' => function() { throw new CloneNotSupportedException('I am *UN*Cloneable'); }
    ]));
  }
}
