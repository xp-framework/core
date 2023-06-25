<?php namespace lang\unittest;

use lang\{IllegalArgumentException, Primitive, XPClass};
use unittest\{Assert, Before, Expect, Test};

abstract class AbstractDefinitionReflectionTest {
  protected $fixture= null;

  /** @return lang.XPClass */
  protected abstract function fixtureClass();

  /** @return object */
  protected abstract function fixtureInstance();

  #[Before]
  public function setUp() {
    $this->fixture= $this->fixtureClass();
  }

  #[Test]
  public function isAGenericDefinition() {
    Assert::true($this->fixture->isGenericDefinition());
  }

  #[Test]
  public function isNotAGeneric() {
    Assert::false($this->fixture->isGeneric());
  }

  #[Test]
  public function components() {
    Assert::equals(['K', 'V'], $this->fixture->genericComponents());
  }

  #[Test]
  public function newGenericTypeIsGeneric() {
    $t= $this->fixture->newGenericType([
      Primitive::$STRING, 
      XPClass::forName('lang.Value')
    ]);
    Assert::true($t->isGeneric());
  }

  #[Test]
  public function newLookupWithStringAndValue() {
    $arguments= [Primitive::$STRING, XPClass::forName('lang.Value')];
    Assert::equals(
      $arguments, 
      $this->fixture->newGenericType($arguments)->genericArguments()
    );
  }

  #[Test]
  public function classesFromReflectionAndCreateAreEqual() {
    Assert::equals(
      typeof($this->fixtureInstance()),
      $this->fixture->newGenericType([
        Primitive::$STRING, 
        XPClass::forName('lang.Value')
      ])
    );
  }

  #[Test]
  public function classesCreatedWithDifferentTypesAreNotEqual() {
    Assert::notEquals(
      $this->fixture->newGenericType([Primitive::$STRING, XPClass::forName('lang.Value')]),
      $this->fixture->newGenericType([Primitive::$STRING, XPClass::forName(self::class)])
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function missingArguments() {
    $this->fixture->newGenericType([]);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function missingArgument() {
    $this->fixture->newGenericType([typeof($this)]);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function tooManyArguments() {
    $c= typeof($this);
    $this->fixture->newGenericType([$c, $c, $c]);
  }

  #[Test]
  public function abstractMethod() {
    $abstractMethod= XPClass::forName('lang.unittest.ArrayFilter')
      ->newGenericType([$this->fixture])
      ->getMethod('accept')
    ;
    Assert::equals(
      $this->fixture,
      $abstractMethod->getParameter(0)->getType()
    );
  }
}