<?php namespace net\xp_framework\unittest\util;

use io\streams\MemoryInputStream;
use unittest\{Test, TestCase};
use util\{RegisteredPropertySource, Properties};

class RegisteredPropertySourceTest extends TestCase {
  protected $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= new RegisteredPropertySource('props', new Properties(null));
  }
  
  #[Test]
  public function doesNotHaveAnyProperties() {
    $this->assertFalse($this->fixture->provides('properties'));
  }

  #[Test]
  public function hasRegisteredProperty() {
    $this->assertTrue($this->fixture->provides('props'));
  }

  #[Test]
  public function returnsRegisteredProperties() {
    $p= new Properties(null);
    $m= new RegisteredPropertySource('name', $p);

    $this->assertTrue($p === $m->fetch('name'));
  }

  #[Test]
  public function equalsReturnsFalseForDifferingName() {
    $p1= new RegisteredPropertySource('name1', new Properties(null));
    $p2= new RegisteredPropertySource('name2', new Properties(null));

    $this->assertNotEquals($p1, $p2);
  }

  #[Test]
  public function equalsReturnsFalseForDifferingProperties() {
    $p1= new RegisteredPropertySource('name1', new Properties(null));
    $p2= new RegisteredPropertySource('name1', (new Properties(null))->load(new MemoryInputStream('[section]')));

    $this->assertNotEquals($p1, $p2);
  }

  #[Test]
  public function equalsReturnsTrueForSameInnerPropertiesAndName() {
    $p1= new RegisteredPropertySource('name1', (new Properties(null))->load(new MemoryInputStream('[section]')));
    $p2= new RegisteredPropertySource('name1', (new Properties(null))->load(new MemoryInputStream('[section]')));

    $this->assertEquals($p1, $p2);
  }
}