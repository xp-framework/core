<?php namespace lang\unittest;

use lang\{Primitive, XPClass, Reflection};
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
      Reflection::type($this->fixture)->property('elements')->constraint()->type()->getName()
    );
  }

  #[Test]
  public function putParameters() {
    $params= Reflection::type($this->fixture)->method('put')->parameters();
    Assert::equals(2, $params->size());
    Assert::equals(Primitive::$STRING, $params->at(0)->constraint()->type());
    Assert::equals(XPClass::forName('lang.Value'), $params->at(1)->constraint()->type());
  }

  #[Test]
  public function getReturnType() {
    Assert::equals(
      'lang.Value',
      Reflection::type($this->fixture)->method('get')->returns()->type()->getName()
    );
  }

  #[Test]
  public function valuesReturnType() {
    Assert::equals(
      'lang.Value[]',
      Reflection::type($this->fixture)->method('values')->returns()->type()->getName()
    );
  }
}