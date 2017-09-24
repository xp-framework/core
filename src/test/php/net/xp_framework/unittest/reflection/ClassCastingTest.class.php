<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\XPClass;
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
    $this->assertEquals($this, typeof($this)->cast($this));
  }

  #[@test]
  public function parentClassCastingThis() {
    $this->assertEquals($this, typeof($this)->getParentClass()->cast($this));
  }

  #[@test]
  public function objectClassCastingThis() {
    $this->assertEquals($this, XPClass::forName('unittest.TestCase')->cast($this));
  }

  #[@test, @expect(ClassCastException::class)]
  public function thisClassCastingAnObject() {
    typeof($this)->cast(new class() { });
  }

  #[@test, @expect(ClassCastException::class)]
  public function thisClassCastingAnUnrelatedClass() {
    typeof($this)->cast(Type::$VOID);
  }

  #[@test]
  public function thisClassCastingNull() {
    $this->assertNull(typeof($this)->cast(null));
  }

  #[@test, @expect(ClassCastException::class)]
  public function castPrimitive() {
    typeof($this)->cast(0);
  }
}
