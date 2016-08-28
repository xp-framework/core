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
  public function thisIsATestCase() {
    $this->assertTrue(XPClass::forName('unittest.TestCase')->isInstance($this));
  }

  #[@test]
  public function thisIsAnInstanceOfThisClass() {
    $this->assertTrue($this->getClass()->isInstance($this));
  }
 
  #[@test]
  public function primitiveStringIsNotAValue() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance('Hello'));
  }

  #[@test]
  public function objectIsNotAValue() {
    $this->assertFalse(XPClass::forName('lang.Value')->isInstance(new \lang\Object()));
  }

  #[@test]
  public function objectIsAGeneric() {
    $this->assertTrue(XPClass::forName('lang.Generic')->isInstance(new \lang\Object()));
  }

  #[@test]
  public function throwableIsAGeneric() {
    $this->assertTrue(XPClass::forName('lang.Generic')->isInstance(new \lang\Throwable('')));
  }

  #[@test]
  public function newInterfaceInstanceIsRunnable() {
    $this->assertTrue(XPClass::forName('lang.Runnable')->isInstance(new class() implements Runnable {
      public function run() { }
    }));
  }

  #[@test]
  public function nullIsNotAnObject() {
    $this->assertFalse(XPClass::forName('lang.Generic')->isInstance(null));
  }
}
