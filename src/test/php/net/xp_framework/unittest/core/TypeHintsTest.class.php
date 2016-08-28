<?php namespace net\xp_framework\unittest\core;

use unittest\actions\RuntimeVersion;
use lang\Generic;
use lang\Object;
use lang\Error;
use lang\IllegalArgumentException;

/**
 * Test type hints.
 */
class TypeHintsTest extends \unittest\TestCase {

  /**
   * Pass an object
   * 
   * @param  lang.Generic $o
   * @return lang.Generic
   */
  protected function pass(Generic $o) { return $o; }

  /**
   * Pass a nullable object
   * 
   * @param  lang.Generic $o
   * @return lang.Generic
   */
  protected function nullable(Generic $o= null) { return $o; }


  #[@test]
  public function pass_an_object() {
    $o= new Object();
    $this->assertEquals($o, $this->pass($o));
  }

  #[@test, @expect(Error::class)]
  public function pass_a_primitive() {
    $this->pass(1);
  }

  #[@test, @expect(Error::class)]
  public function pass_null() {
    $this->pass(null);
  }

  #[@test]
  public function pass_object_to_nullable() {
    $o= new Object();
    $this->assertEquals($o, $this->nullable($o));
  }

  #[@test, @expect(Error::class)]
  public function pass_a_primitive_to_nullable() {
    $this->nullable(1);
  }


  #[@test]
  public function pass_null_to_nullable() {
    $this->assertEquals(null, $this->nullable(null));
  }
}
