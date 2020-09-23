<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassCastException, Type, XPClass};
use unittest\{Expect, Test, TestCase};

/**
 * TestCase
 *
 * @see      xp://lang.XPClass#cast
 */
class ClassCastingTest extends TestCase {

  #[Test]
  public function thisClassCastingThis() {
    $this->assertEquals($this, typeof($this)->cast($this));
  }

  #[Test]
  public function parentClassCastingThis() {
    $this->assertEquals($this, typeof($this)->getParentClass()->cast($this));
  }

  #[Test]
  public function objectClassCastingThis() {
    $this->assertEquals($this, XPClass::forName('unittest.TestCase')->cast($this));
  }

  #[Test, Expect(ClassCastException::class)]
  public function thisClassCastingAnObject() {
    typeof($this)->cast(new class() { });
  }

  #[Test, Expect(ClassCastException::class)]
  public function thisClassCastingAnUnrelatedClass() {
    typeof($this)->cast(Type::$VOID);
  }

  #[Test]
  public function thisClassCastingNull() {
    $this->assertNull(typeof($this)->cast(null));
  }

  #[Test, Expect(ClassCastException::class)]
  public function castPrimitive() {
    typeof($this)->cast(0);
  }
}