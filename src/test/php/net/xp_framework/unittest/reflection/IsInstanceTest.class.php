<?php namespace net\xp_framework\unittest\reflection;

use lang\{Runnable, XPClass};
use unittest\Test;

/**
 * TestCase
 *
 * @see      xp://lang.XPClass#isInstance
 */
class IsInstanceTest extends \unittest\TestCase {

  #[Test]
  public function this_is_an_instance_of_testcase() {
    $this->assertTrue(XPClass::forName('unittest.TestCase')->isInstance($this));
  }

  #[Test]
  public function this_is_an_instance_of_this_class() {
    $this->assertTrue(typeof($this)->isInstance($this));
  }
 
  #[Test]
  public function primitive_string_is_not_value() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance('Hello'));
  }

  #[Test]
  public function this_is_a_value() {
    $this->assertTrue(XPClass::forName('lang.Value')->isInstance($this));
  }

  #[Test]
  public function new_interface_instance_is_rsunnable() {
    $this->assertTrue(XPClass::forName('lang.Runnable')->isInstance(new class() implements Runnable {
      public function run() { }
    }));
  }

  #[Test]
  public function null_is_not_a_value() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance(null));
  }
}