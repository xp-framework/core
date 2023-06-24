<?php namespace net\xp_framework\unittest\core\generics;

use lang\{Primitive, XPClass};
use unittest\Assert;
use unittest\{Test, TestCase};

/**
 * TestCase for instance reflection
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class InstanceReflectionTest {
  private $fixture;
  
  /**
   * Creates fixture, a Lookup with String and TestCase as component types.
   *
   * @return void
   */  
  #[Before]
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
  }

  #[Test]
  public function nameof() {
    Assert::equals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>',
      nameof($this->fixture)
    );
  }

  #[Test]
  public function nameOfClass() {
    Assert::equals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>', 
      typeof($this->fixture)->getName()
    );
  }

  #[Test]
  public function simpleNameOfClass() {
    Assert::equals(
      'Lookup<string,unittest.TestCase>', 
      typeof($this->fixture)->getSimpleName()
    );
  }

  #[Test]
  public function reflectedNameOfClass() {
    Assert::equals(
      "net\\xp_framework\\unittest\\core\\generics\\Lookup\xb7\xb7\xfestring\xb8unittest\xa6TestCase",
      typeof($this->fixture)->literal()
    );
  }

  #[Test]
  public function instanceIsGeneric() {
    Assert::true(typeof($this->fixture)->isGeneric());
  }

  #[Test]
  public function instanceIsNoGenericDefinition() {
    Assert::false(typeof($this->fixture)->isGenericDefinition());
  }

  #[Test]
  public function genericDefinition() {
    Assert::equals(
      XPClass::forName('net.xp_framework.unittest.core.generics.Lookup'),
      typeof($this->fixture)->genericDefinition()
    );
  }

  #[Test]
  public function genericArguments() {
    Assert::equals(
      [Primitive::$STRING, XPClass::forName('unittest.TestCase')],
      typeof($this->fixture)->genericArguments()
    );
  }

  #[Test]
  public function elementFieldType() {
    Assert::equals(
      '[:unittest.TestCase]',
      typeof($this->fixture)->getField('elements')->getTypeName()
    );
  }

  #[Test]
  public function putParameters() {
    $params= typeof($this->fixture)->getMethod('put')->getParameters();
    Assert::equals(2, sizeof($params));
    Assert::equals(Primitive::$STRING, $params[0]->getType());
    Assert::equals(XPClass::forName('unittest.TestCase'), $params[1]->getType());
  }

  #[Test]
  public function getReturnType() {
    Assert::equals(
      'unittest.TestCase',
      typeof($this->fixture)->getMethod('get')->getReturnTypeName()
    );
  }

  #[Test]
  public function valuesReturnType() {
    Assert::equals(
      'unittest.TestCase[]',
      typeof($this->fixture)->getMethod('values')->getReturnTypeName()
    );
  }
}