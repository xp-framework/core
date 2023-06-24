<?php namespace net\xp_framework\unittest\core\generics;

use lang\{IllegalArgumentException, Primitive, XPClass};
use unittest\Assert;
use unittest\{Expect, Test};

/**
 * TestCase for definition reflection
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
abstract class AbstractDefinitionReflectionTest {
  protected $fixture= null;

  /**
   * Creates fixture, a Lookup class
   *
   * @return  lang.XPClass
   */  
  protected abstract function fixtureClass();

  /**
   * Creates fixture instance
   *
   * @return  var
   */
  protected abstract function fixtureInstance();

  /**
   * Creates fixture, a Lookup class
   */  
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
      XPClass::forName('unittest.TestCase')
    ]);
    Assert::true($t->isGeneric());
  }

  #[Test]
  public function newLookupWithStringAndTestCase() {
    $arguments= [Primitive::$STRING, XPClass::forName('unittest.TestCase')];
    Assert::equals(
      $arguments, 
      $this->fixture->newGenericType($arguments)->genericArguments()
    );
  }

  #[Test]
  public function newLookupWithStringAndObject() {
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
        XPClass::forName('unittest.TestCase')
      ])
    );
  }

  #[Test]
  public function classesCreatedWithDifferentTypesAreNotEqual() {
    Assert::notEquals(
      $this->fixture->newGenericType([Primitive::$STRING, XPClass::forName('lang.Value')]),
      $this->fixture->newGenericType([Primitive::$STRING, XPClass::forName('unittest.TestCase')])
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
    $abstractMethod= XPClass::forName('net.xp_framework.unittest.core.generics.ArrayFilter')
      ->newGenericType([$this->fixture])
      ->getMethod('accept')
    ;
    Assert::equals(
      $this->fixture,
      $abstractMethod->getParameter(0)->getType()
    );
  }
}