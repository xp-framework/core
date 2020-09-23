<?php namespace net\xp_framework\unittest\core\generics;

use unittest\{Test, TestCase};

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Nullable
 */
class OptionalArgTest extends TestCase {

  #[Test]
  public function create_with_value() {
    $this->assertEquals($this, create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', $this)->get());
  }

  #[Test]
  public function create_with_null() {
    $this->assertFalse(create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', null)->hasValue());
  }

  #[Test]
  public function set_value() {
    $this->assertEquals($this, create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', $this)->set($this)->get());
  }

  #[Test]
  public function set_null() {
    $this->assertFalse(create('new net.xp_framework.unittest.core.generics.Nullable<unittest.TestCase>', $this)->set(null)->hasValue());
  }
}