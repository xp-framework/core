<?php namespace net\xp_framework\unittest\core;

use unittest\actions\RuntimeVersion;
use lang\{Error, Value, IllegalArgumentException};

/**
 * Test type hints.
 */
class TypeHintsTest extends \unittest\TestCase {

  /**
   * Pass an object
   * 
   * @param  lang.Value $o
   * @return lang.Value
   */
  protected function pass(Value $o) { return $o; }

  /**
   * Pass a nullable object
   * 
   * @param  lang.Value $o
   * @return lang.Value
   */
  protected function nullable(Value $o= null) { return $o; }


  #[@test]
  public function pass_an_object() {
    $o= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 'Test'; }
      public function compareTo($value) { return $this <=> $value; }
    };
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
    $o= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 'Test'; }
      public function compareTo($value) { return $this <=> $value; }
    };
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
