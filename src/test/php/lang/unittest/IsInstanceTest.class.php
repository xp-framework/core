<?php namespace lang\unittest;

use lang\{Runnable, XPClass};
use test\{Assert, Test};

class IsInstanceTest extends BaseTest {

  #[Test]
  public function this_is_an_instance_of_testcase() {
    Assert::true(XPClass::forName(BaseTest::class)->isInstance($this));
  }

  #[Test]
  public function this_is_an_instance_of_this_class() {
    Assert::true(typeof($this)->isInstance($this));
  }
 
  #[Test]
  public function primitive_string_is_not_value() {
    Assert::false(XPClass::forName('lang.Value')->isInstance('Hello'));
  }

  #[Test]
  public function name_is_a_value() {
    Assert::true(XPClass::forName('lang.Value')->isInstance(new Name('Test')));
  }

  #[Test]
  public function new_interface_instance_is_rsunnable() {
    Assert::true(XPClass::forName('lang.Runnable')->isInstance(new class() implements Runnable {
      public function run() { }
    }));
  }

  #[Test]
  public function null_is_not_a_value() {
    Assert::false(XPClass::forName('lang.Value')->isInstance(null));
  }
}