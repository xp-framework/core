<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\XPClass;
use lang\Object;
use lang\types\String;

/**
 * TestCase
 *
 * @see      xp://lang.XPClass#cast
 */
class ClassCastingTest extends TestCase {

  #[@test]
  public function thisClassCastingThis() {
    $this->assertEquals($this, $this->getClass()->cast($this));
  }

  #[@test]
  public function parentClassCastingThis() {
    $this->assertEquals($this, $this->getClass()->getParentClass()->cast($this));
  }

  #[@test]
  public function objectClassCastingThis() {
    $this->assertEquals($this, XPClass::forName('lang.Object')->cast($this));
  }

  #[@test, @expect('lang.ClassCastException')]
  public function thisClassCastingAnObject() {
    $this->getClass()->cast(new Object());
  }

  #[@test, @expect('lang.ClassCastException')]
  public function thisClassCastingAnUnrelatedClass() {
    $this->getClass()->cast(new String('Hello'));
  }

  #[@test]
  public function thisClassCastingNull() {
    $this->assertNull($this->getClass()->cast(null));
  }

  #[@test, @expect('lang.ClassCastException')]
  public function castPrimitive() {
    $this->getClass()->cast(0);
  }
}
