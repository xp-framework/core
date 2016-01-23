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
      $this->fixture->getClass()->getName()
    );
  }

  #[@test]
  public function simpleNameOfClass() {
    $this->assertEquals(
      'Lookup<string,unittest.TestCase>', 
      $this->fixture->getClass()->getSimpleName()
    );
  }

  #[@test]
  public function reflectedNameOfClass() {
    $class= $this->fixture->getClass();
    $this->assertEquals(
      'net\xp_framework\unittest\core\generics\Lookup··þstring¸unittest¦TestCase', 
      literal($class->getName())
    );
  }

  #[@test]
  public function instanceIsGeneric() {
    $this->assertTrue($this->fixture->getClass()->isGeneric());
  }

  #[@test]
  public function instanceIsNoGenericDefinition() {
    $this->assertFalse($this->fixture->getClass()->isGenericDefinition());
  }

  #[@test]
  public function genericDefinition() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Lookup'),
      $this->fixture->getClass()->genericDefinition()
    );
  }

  #[@test]
  public function genericArguments() {
    $this->assertEquals(
      [Primitive::$STRING, XPClass::forName('unittest.TestCase')],
      $this->fixture->getClass()->genericArguments()
    );
  }

  #[@test, @ignore('No longer existant in new implementation')]
  public function delegateFieldType() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup',
      $this->fixture->getClass()->getField('delegate')->getType()
    );
  }

  #[@test]
  public function putParameters() {
    $params= $this->fixture->getClass()->getMethod('put')->getParameters();
    $this->assertEquals(2, sizeof($params));
    $this->assertEquals(Primitive::$STRING, $params[0]->getType());
    $this->assertEquals(XPClass::forName('unittest.TestCase'), $params[1]->getType());
  }

  #[@test]
  public function getReturnType() {
    $this->assertEquals(
      'unittest.TestCase',
      $this->fixture->getClass()->getMethod('get')->getReturnTypeName()
    );
  }

  #[@test]
  public function valuesReturnType() {
    $this->assertEquals(
      'unittest.TestCase[]',
      $this->fixture->getClass()->getMethod('values')->getReturnTypeName()
    );
  }
}
