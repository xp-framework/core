<?php namespace net\xp_framework\unittest\core\generics;

use lang\Primitive;
use lang\XPClass;

/**
 * TestCase for instance reflection
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class InstanceReflectionTest extends \unittest\TestCase {
  private $fixture;
  
  /**
   * Creates fixture, a Lookup with String and TestCase as component types.
   *
   * @return void
   */  
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
  }

  #[@test]
  public function nameof() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>',
      nameof($this->fixture)
    );
  }

  #[@test]
  public function nameOfClass() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>', 
      typeof($this->fixture)->getName()
    );
  }

  #[@test]
  public function simpleNameOfClass() {
    $this->assertEquals(
      'Lookup<string,unittest.TestCase>', 
      typeof($this->fixture)->getSimpleName()
    );
  }

  #[@test]
  public function reflectedNameOfClass() {
    $class= typeof($this->fixture);
    $this->assertEquals(
      'net\xp_framework\unittest\core\generics\Lookup··þstring¸unittest¦TestCase', 
      literal($class->getName())
    );
  }

  #[@test]
  public function instanceIsGeneric() {
    $this->assertTrue(typeof($this->fixture)->isGeneric());
  }

  #[@test]
  public function instanceIsNoGenericDefinition() {
    $this->assertFalse(typeof($this->fixture)->isGenericDefinition());
  }

  #[@test]
  public function genericDefinition() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Lookup'),
      typeof($this->fixture)->genericDefinition()
    );
  }

  #[@test]
  public function genericArguments() {
    $this->assertEquals(
      [Primitive::$STRING, XPClass::forName('unittest.TestCase')],
      typeof($this->fixture)->genericArguments()
    );
  }

  #[@test]
  public function elementFieldType() {
    $this->assertEquals(
      '[:unittest.TestCase]',
      typeof($this->fixture)->getField('elements')->getTypeName()
    );
  }

  #[@test]
  public function putParameters() {
    $params= typeof($this->fixture)->getMethod('put')->getParameters();
    $this->assertEquals(2, sizeof($params));
    $this->assertEquals(Primitive::$STRING, $params[0]->getType());
    $this->assertEquals(XPClass::forName('unittest.TestCase'), $params[1]->getType());
  }

  #[@test]
  public function getReturnType() {
    $this->assertEquals(
      'unittest.TestCase',
      typeof($this->fixture)->getMethod('get')->getReturnTypeName()
    );
  }

  #[@test]
  public function valuesReturnType() {
    $this->assertEquals(
      'unittest.TestCase[]',
      typeof($this->fixture)->getMethod('values')->getReturnTypeName()
    );
  }
}
