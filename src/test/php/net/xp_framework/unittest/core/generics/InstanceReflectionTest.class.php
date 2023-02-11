<?php namespace net\xp_framework\unittest\core\generics;

use lang\{Primitive, XPClass};
use unittest\{Test, TestCase};

/**
 * TestCase for instance reflection
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class InstanceReflectionTest extends TestCase {
  private $fixture;
  
  /**
   * Creates fixture, a Lookup with String and TestCase as component types.
   *
   * @return void
   */  
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
  }

  #[Test]
  public function nameof() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>',
      nameof($this->fixture)
    );
  }

  #[Test]
  public function nameOfClass() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>', 
      typeof($this->fixture)->getName()
    );
  }

  #[Test]
  public function simpleNameOfClass() {
    $this->assertEquals(
      'Lookup<string,unittest.TestCase>', 
      typeof($this->fixture)->getSimpleName()
    );
  }

  #[Test]
  public function reflectedNameOfClass() {
    $this->assertEquals(
      "net\\xp_framework\\unittest\\core\\generics\\Lookup\xabstring\xb8unittest\x98TestCase\xbb",
      typeof($this->fixture)->literal()
    );
  }

  #[Test]
  public function instanceIsGeneric() {
    $this->assertTrue(typeof($this->fixture)->isGeneric());
  }

  #[Test]
  public function instanceIsNoGenericDefinition() {
    $this->assertFalse(typeof($this->fixture)->isGenericDefinition());
  }

  #[Test]
  public function genericDefinition() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Lookup'),
      typeof($this->fixture)->genericDefinition()
    );
  }

  #[Test]
  public function genericArguments() {
    $this->assertEquals(
      [Primitive::$STRING, XPClass::forName('unittest.TestCase')],
      typeof($this->fixture)->genericArguments()
    );
  }

  #[Test]
  public function elementFieldType() {
    $this->assertEquals(
      '[:unittest.TestCase]',
      typeof($this->fixture)->getField('elements')->getTypeName()
    );
  }

  #[Test]
  public function putParameters() {
    $params= typeof($this->fixture)->getMethod('put')->getParameters();
    $this->assertEquals(2, sizeof($params));
    $this->assertEquals(Primitive::$STRING, $params[0]->getType());
    $this->assertEquals(XPClass::forName('unittest.TestCase'), $params[1]->getType());
  }

  #[Test]
  public function getReturnType() {
    $this->assertEquals(
      'unittest.TestCase',
      typeof($this->fixture)->getMethod('get')->getReturnTypeName()
    );
  }

  #[Test]
  public function valuesReturnType() {
    $this->assertEquals(
      'unittest.TestCase[]',
      typeof($this->fixture)->getMethod('values')->getReturnTypeName()
    );
  }
}