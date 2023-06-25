<?php namespace lang\unittest;

use lang\{IllegalArgumentException, Primitive, XPClass, Value};
use unittest\{Assert, Expect, Test};

class PrimitivesTest {

  #[Test]
  public function primitiveStringKey() {
    $l= create('new lang.unittest.Lookup<string, lang.unittest.PrimitivesTest>', [
      'this' => $this
    ]);
    Assert::equals($this, $l->get('this'));
  }

  #[Test]
  public function primitiveStringValue() {
    $l= create('new lang.unittest.Lookup<lang.unittest.PrimitivesTest, string>()');
    $l->put($this, 'this');
    Assert::equals('this', $l->get($this));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function primitiveVerification() {
    $l= create('new lang.unittest.Lookup<string, lang.unittest.PrimitivesTest>()');
    $l->put(1, $this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function instanceVerification() {
    $l= create('new lang.unittest.Lookup<string, lang.unittest.PrimitivesTest>()');
    $l->put($this, $this);
  }

  #[Test]
  public function nameOfClass() {
    $type= XPClass::forName('lang.unittest.Lookup')->newGenericType([
      Primitive::$STRING,
      XPClass::forName('lang.unittest.PrimitivesTest')
    ]);
    Assert::equals('lang.unittest.Lookup<string,lang.unittest.PrimitivesTest>', $type->getName());
  }

  #[Test]
  public function typeArguments() {
    Assert::equals(
      [Primitive::$STRING, XPClass::forName('lang.unittest.PrimitivesTest')],
      typeof(create('new lang.unittest.Lookup<string, lang.unittest.PrimitivesTest>()'))->genericArguments()
    );
  }
}