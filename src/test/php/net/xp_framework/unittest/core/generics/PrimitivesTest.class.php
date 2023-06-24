<?php namespace net\xp_framework\unittest\core\generics;

use lang\{IllegalArgumentException, Primitive, XPClass, Value};
use unittest\{Assert, Expect, Test};

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class PrimitivesTest {

  #[Test]
  public function primitiveStringKey() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, net.xp_framework.unittest.core.generics.PrimitivesTest>', [
      'this' => $this
    ]);
    Assert::equals($this, $l->get('this'));
  }

  #[Test]
  public function primitiveStringValue() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<net.xp_framework.unittest.core.generics.PrimitivesTest, string>()');
    $l->put($this, 'this');
    Assert::equals('this', $l->get($this));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function primitiveVerification() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, net.xp_framework.unittest.core.generics.PrimitivesTest>()');
    $l->put(1, $this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function instanceVerification() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, net.xp_framework.unittest.core.generics.PrimitivesTest>()');
    $l->put($this, $this);
  }

  #[Test]
  public function nameOfClass() {
    $type= XPClass::forName('net.xp_framework.unittest.core.generics.Lookup')->newGenericType([
      Primitive::$STRING,
      XPClass::forName('net.xp_framework.unittest.core.generics.PrimitivesTest')
    ]);
    Assert::equals('net.xp_framework.unittest.core.generics.Lookup<string,net.xp_framework.unittest.core.generics.PrimitivesTest>', $type->getName());
  }

  #[Test]
  public function typeArguments() {
    Assert::equals(
      [Primitive::$STRING, XPClass::forName('net.xp_framework.unittest.core.generics.PrimitivesTest')],
      typeof(create('new net.xp_framework.unittest.core.generics.Lookup<string, net.xp_framework.unittest.core.generics.PrimitivesTest>()'))->genericArguments()
    );
  }
}