<?php namespace net\xp_framework\unittest\core\generics;

use lang\XPClass;
use lang\Primitive;
use lang\IllegalArgumentException;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class PrimitivesTest extends \unittest\TestCase {

  #[@test]
  public function primitiveStringKey() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>', [
      'this' => $this
    ]);
    $this->assertEquals($this, $l->get('this'));
  }

  #[@test]
  public function primitiveStringValue() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<unittest.TestCase, string>()');
    $l->put($this, 'this');
    $this->assertEquals('this', $l->get($this));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function primitiveVerification() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
    $l->put(1, $this);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function instanceVerification() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
    $l->put($this, $this);
  }

  #[@test]
  public function nameOfClass() {
    $type= XPClass::forName('net.xp_framework.unittest.core.generics.Lookup')->newGenericType([
      Primitive::$STRING,
      XPClass::forName('unittest.TestCase')
    ]);
    $this->assertEquals('net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>', $type->getName());
  }

  #[@test]
  public function typeArguments() {
    $this->assertEquals(
      [Primitive::$STRING, XPClass::forName('unittest.TestCase')],
      typeof(create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()'))->genericArguments()
    );
  }
}
