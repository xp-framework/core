<?php namespace net\xp_framework\unittest\core;

use lang\IllegalAccessException;
use lang\NullPointerException;

/**
 * Tests the "NULL-safe" xp::null.
 *
 * @deprecated
 */
class NullTest extends \unittest\TestCase {

  #[@test]
  public function isNull() {
    $this->assertTrue(is(null, \xp::null()));
  }

  #[@test]
  public function isNotAnObject() {
    $this->assertFalse(is('lang.Generic', \xp::null()));
  }

  #[@test]
  public function typeOf() {
    $this->assertEquals('<null>', \xp::typeOf(\xp::null()));
  }
  
  #[@test]
  public function stringOf() {
    $this->assertEquals('<null>', \xp::stringOf(\xp::null()));
  }
  
  #[@test, @expect(IllegalAccessException::class)]
  public function newInstance() {
    new \__null();
  }

  #[@test, @expect(NullPointerException::class)]
  public function cloneNull() {
    clone(\xp::null());
  }

  #[@test, @expect(NullPointerException::class)]
  public function methodInvocation() {
    $null= \xp::null();
    $null->method();
  }

  #[@test, @expect(NullPointerException::class)]
  public function memberReadAccess() {
    $null= \xp::null();
    $i= $null->member;
  }
  
  #[@test, @expect(NullPointerException::class)]
  public function memberWriteccess() {
    $null= \xp::null();
    $null->member= 15;
  }
}
