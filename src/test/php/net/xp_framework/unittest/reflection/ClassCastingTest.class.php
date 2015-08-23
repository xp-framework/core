<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\XPClass;
use lang\Object;
use lang\Type;
use lang\ClassCastException;

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

  #[@test, @expect(ClassCastException::class)]
  public function thisClassCastingAnObject() {
    $this->getClass()->cast(new Object());
  }

  #[@test, @expect(ClassCastException::class)]
  public function thisClassCastingAnUnrelatedClass() {
    $this->getClass()->cast(Type::$VOID);
  }

  #[@test]
  public function thisClassCastingNull() {
    $this->assertNull($this->getClass()->cast(null));
  }

  #[@test, @expect(ClassCastException::class)]
  public function castPrimitive() {
    $this->getClass()->cast(0);
  }
}
