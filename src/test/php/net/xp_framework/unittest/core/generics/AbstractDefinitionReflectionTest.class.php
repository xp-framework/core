<?php namespace net\xp_framework\unittest\core\generics;

use lang\{IllegalArgumentException, Primitive, XPClass};
use unittest\{Expect, Test};

/**
 * TestCase for definition reflection
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
abstract class AbstractDefinitionReflectionTest extends \unittest\TestCase {
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
  public function setUp() {
    $this->fixture= $this->fixtureClass();
  }

  #[Test]
  public function isAGenericDefinition() {
    $this->assertTrue($this->fixture->isGenericDefinition());
  }

  #[Test]
  public function isNotAGeneric() {
    $this->assertFalse($this->fixture->isGeneric());
  }

  #[Test]
  public function components() {
    $this->assertEquals(['K', 'V'], $this->fixture->genericComponents());
  }

  #[Test]
  public function newGenericTypeIsGeneric() {
    $t= $this->fixture->newGenericType([
      Primitive::$STRING, 
      XPClass::forName('unittest.TestCase')
    ]);
    $this->assertTrue($t->isGeneric());
  }

  #[Test]
  public function newLookupWithStringAndTestCase() {
    $arguments= [Primitive::$STRING, XPClass::forName('unittest.TestCase')];
    $this->assertEquals(
      $arguments, 
      $this->fixture->newGenericType($arguments)->genericArguments()
    );
  }

  #[Test]
  public function newLookupWithStringAndObject() {
    $arguments= [Primitive::$STRING, XPClass::forName('lang.Value')];
    $this->assertEquals(
      $arguments, 
      $this->fixture->newGenericType($arguments)->genericArguments()
    );
  }

  #[Test]
  public function classesFromReflectionAndCreateAreEqual() {
    $this->assertEquals(
      typeof($this->fixtureInstance()),
      $this->fixture->newGenericType([
        Primitive::$STRING, 
        XPClass::forName('unittest.TestCase')
      ])
    );
  }

  #[Test]
  public function classesCreatedWithDifferentTypesAreNotEqual() {
    $this->assertNotEquals(
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
    $this->assertEquals(
      $this->fixture,
      $abstractMethod->getParameter(0)->getType()
    );
  }
}