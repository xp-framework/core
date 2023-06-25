<?php namespace lang\unittest;

use lang\{ClassCastException, Type, XPClass};
use net\xp_framework\unittest\BaseTest;
use unittest\{Assert, Expect, Test};

class ClassCastingTest extends BaseTest {

  #[Test]
  public function thisClassCastingThis() {
    Assert::equals($this, typeof($this)->cast($this));
  }

  #[Test]
  public function parentClassCastingThis() {
    Assert::equals($this, typeof($this)->getParentClass()->cast($this));
  }

  #[Test]
  public function objectClassCastingThis() {
    Assert::equals($this, XPClass::forName(self::class)->cast($this));
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
    Assert::null(typeof($this)->cast(null));
  }

  #[Test, Expect(ClassCastException::class)]
  public function castPrimitive() {
    typeof($this)->cast(0);
  }
}