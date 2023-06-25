<?php namespace lang\unittest;

use lang\{IllegalArgumentException, Primitive, XPClass, Reflection};
use test\{Assert, Before, Expect, Ignore, Test};

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

  #[Test, Ignore('Unsupported')]
  public function abstractMethod() {
    $generic= XPClass::forName('lang.unittest.ArrayFilter')->newGenericType([$this->fixture]);
    Assert::equals(
      $this->fixture,
      Reflection::type($generic)->method('accept')->parameter(0)->constraint()->type()
    );
  }
}