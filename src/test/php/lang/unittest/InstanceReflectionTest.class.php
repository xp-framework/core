<?php namespace lang\unittest;

use lang\{Primitive, XPClass};
use test\{Assert, Before, Test};

class InstanceReflectionTest {
  private $fixture;
  
  #[Before]
  public function setUp() {
    $this->fixture= create('new lang.unittest.Lookup<string, lang.Value>()');
  }

  #[Test]
  public function nameof() {
    Assert::equals(
      'lang.unittest.Lookup<string,lang.Value>',
      nameof($this->fixture)
    );
  }

  #[Test]
  public function nameOfClass() {
    Assert::equals(
      'lang.unittest.Lookup<string,lang.Value>', 
      typeof($this->fixture)->getName()
    );
  }

  #[Test]
  public function simpleNameOfClass() {
    Assert::equals(
      'Lookup<string,lang.Value>', 
      typeof($this->fixture)->getSimpleName()
    );
  }

  #[Test]
  public function reflectedNameOfClass() {
    Assert::equals(
      "lang\\unittest\\Lookup\xb7\xb7\xfestring\xb8lang\xa6Value",
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
      XPClass::forName('lang.unittest.Lookup'),
      typeof($this->fixture)->genericDefinition()
    );
  }

  #[Test]
  public function genericArguments() {
    Assert::equals(
      [Primitive::$STRING, XPClass::forName('lang.Value')],
      typeof($this->fixture)->genericArguments()
    );
  }

  #[Test]
  public function elementFieldType() {
    Assert::equals(
      '[:lang.Value]',
      typeof($this->fixture)->getField('elements')->getTypeName()
    );
  }

  #[Test]
  public function putParameters() {
    $params= typeof($this->fixture)->getMethod('put')->getParameters();
    Assert::equals(2, sizeof($params));
    Assert::equals(Primitive::$STRING, $params[0]->getType());
    Assert::equals(XPClass::forName('lang.Value'), $params[1]->getType());
  }

  #[Test]
  public function getReturnType() {
    Assert::equals(
      'lang.Value',
      typeof($this->fixture)->getMethod('get')->getReturnTypeName()
    );
  }

  #[Test]
  public function valuesReturnType() {
    Assert::equals(
      'lang.Value[]',
      typeof($this->fixture)->getMethod('values')->getReturnTypeName()
    );
  }
}