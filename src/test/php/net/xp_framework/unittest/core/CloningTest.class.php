<?php namespace net\xp_framework\unittest\core;

use unittest\TestCase;
use lang\Object;
use lang\CloneNotSupportedException;

/**
 * Tests cloning functionality
 */
class CloningTest extends TestCase {

  /** @deprecated */
  #[@test, @expect('lang.NullPointerException')]
  public function cloningOfNulls() {
    clone(\xp::null());
  }

  #[@test]
  public function cloneOfObject() {
    $original= new \lang\Object();
    $this->assertFalse($original == clone($original));
  }

  #[@test]
  public function cloneInterceptorCalled() {
    $original= newinstance(Object::class, [], '{
      public $cloned= FALSE;

      public function __clone() {
        $this->cloned= true;
      }
    }');
    $this->assertFalse($original->cloned);
    $clone= clone($original);
    $this->assertFalse($original->cloned);
    $this->assertTrue($clone->cloned);
  }

  #[@test, @expect('lang.CloneNotSupportedException')]
  public function cloneInterceptorThrowsException() {
    clone(newinstance(Object::class, [], '{
      public function __clone() {
        throw new CloneNotSupportedException("I am *UN*Cloneable");
      }
    }'));
  }
}
