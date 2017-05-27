<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Runnable;

/**
 * TestCase
 *
 * @see      xp://lang.XPClass#isInstance
 */
class IsInstanceTest extends \unittest\TestCase {

  #[@test]
  public function this_is_an_instance_of_testcase() {
    $this->assertTrue(XPClass::forName('unittest.TestCase')->isInstance($this));
  }

  #[@test]
  public function this_is_an_instance_of_this_class() {
    $this->assertTrue(typeof($this)->isInstance($this));
  }
 
  #[@test]
  public function primitive_string_is_not_value() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance('Hello'));
  }

  #[@test]
  public function this_is_not_a_value() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance($this));
  }

  #[@test]
  public function new_interface_instance_is_rsunnable() {
    $this->assertTrue(XPClass::forName('lang.Runnable')->isInstance(new class() implements Runnable {
      public function run() { }
    }));
  }

  #[@test]
  public function null_is_not_a_value() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance(null));
  }
}
