<?php namespace net\xp_framework\unittest\core\generics;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Nullable
 */
class OptionalArgTest extends \unittest\TestCase {

  #[@test]
  public function create_with_value() {
    $this->assertEquals($this, create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', $this)->get());
  }

  #[@test]
  public function create_with_null() {
    $this->assertFalse(create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', null)->hasValue());
  }

  #[@test]
  public function set_value() {
    $this->assertEquals($this, create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', $this)->set($this)->get());
  }

  #[@test]
  public function set_null() {
    $this->assertFalse(create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', $this)->set(null)->hasValue());
  }
}
