<?php namespace net\xp_framework\unittest\core\generics;

use lang\{IllegalArgumentException, Primitive, XPClass};
use unittest\{Expect, Test, TestCase};

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class PrimitivesTest extends TestCase {

  #[Test]
  public function primitiveStringKey() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>', [
      'this' => $this
    ]);
    $this->assertEquals($this, $l->get('this'));
  }

  #[Test]
  public function primitiveStringValue() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<unittest.TestCase, string>()');
    $l->put($this, 'this');
    $this->assertEquals('this', $l->get($this));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function primitiveVerification() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
    $l->put(1, $this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function instanceVerification() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
    $l->put($this, $this);
  }

  #[Test]
  public function nameOfClass() {
    $type= XPClass::forName('net.xp_framework.unittest.core.generics.Lookup')->newGenericType([
      Primitive::$STRING,
      XPClass::forName('unittest.TestCase')
    ]);
    $this->assertEquals('net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>', $type->getName());
  }

  #[Test]
  public function typeArguments() {
    $this->assertEquals(
      [Primitive::$STRING, XPClass::forName('unittest.TestCase')],
      typeof(create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()'))->genericArguments()
    );
  }
}