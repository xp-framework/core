<?php namespace net\xp_framework\unittest\util;

use io\streams\MemoryInputStream;
use unittest\Assert;
use unittest\{Test, TestCase};
use util\{Properties, RegisteredPropertySource};

class RegisteredPropertySourceTest {
  protected $fixture;

  /** @return void */
  #[Before]
  public function setUp() {
    $this->fixture= new RegisteredPropertySource('props', new Properties(null));
  }
  
  #[Test]
  public function doesNotHaveAnyProperties() {
    Assert::false($this->fixture->provides('properties'));
  }

  #[Test]
  public function hasRegisteredProperty() {
    Assert::true($this->fixture->provides('props'));
  }

  #[Test]
  public function returnsRegisteredProperties() {
    $p= new Properties(null);
    $m= new RegisteredPropertySource('name', $p);

    Assert::true($p === $m->fetch('name'));
  }

  #[Test]
  public function equalsReturnsFalseForDifferingName() {
    $p1= new RegisteredPropertySource('name1', new Properties(null));
    $p2= new RegisteredPropertySource('name2', new Properties(null));

    Assert::notEquals($p1, $p2);
  }

  #[Test]
  public function equalsReturnsFalseForDifferingProperties() {
    $p1= new RegisteredPropertySource('name1', new Properties(null));
    $p2= new RegisteredPropertySource('name1', (new Properties(null))->load(new MemoryInputStream('[section]')));

    Assert::notEquals($p1, $p2);
  }

  #[Test]
  public function equalsReturnsTrueForSameInnerPropertiesAndName() {
    $p1= new RegisteredPropertySource('name1', (new Properties(null))->load(new MemoryInputStream('[section]')));
    $p2= new RegisteredPropertySource('name1', (new Properties(null))->load(new MemoryInputStream('[section]')));

    Assert::equals($p1, $p2);
  }
}