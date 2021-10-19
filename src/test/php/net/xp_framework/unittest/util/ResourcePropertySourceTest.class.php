<?php namespace net\xp_framework\unittest\util;

use unittest\{Test, TestCase};
use util\{Properties, ResourcePropertySource};
new import('lang.ResourceProvider');

/**
 * Test for ResourcePropertySource
 *
 * @deprecated
 * @see      xp://util.ResourcePropertySource
 */
class ResourcePropertySourceTest extends TestCase {
  protected $fixture= null;

  public function setUp() {
    $this->fixture= new ResourcePropertySource('net/xp_framework/unittest/util');
  }

  /**
   * Test
   *
   */
  #[Test]
  public function doesNotHaveProperty() {
    $this->assertFalse($this->fixture->provides('non-existent'));
  }

  /**
   * Test
   *
   */
  #[Test]
  public function hasProperty() {
    $this->assertTrue($this->fixture->provides('example'));
  }

  /**
   * Test
   *
   * Relies on a file "example.ini" existing parallel to this class
   */
  #[Test]
  public function returnsProperties() {
    $this->assertEquals('value', $this->fixture->fetch('example')->readString('section', 'key'));
  }

  /**
   * Test
   *
   */
  #[Test]
  public function sourcesAreEqual() {
    $p1= new ResourcePropertySource('net/xp_framework/unittest/util');
    $p2= new ResourcePropertySource('net/xp_framework/unittest/util');

    $this->assertEquals($p1, $p2);
  }

  /**
   * Test
   *
   * Relies on a file "example.ini" existing parallel to this class
   */
  #[Test]
  public function propertiesFromSameResourceAreEqual() {
    $p1= new ResourcePropertySource('net/xp_framework/unittest/util');
    $p2= new ResourcePropertySource('net/xp_framework/unittest/util');

    $this->assertEquals($p1->fetch('example'), $p2->fetch('example'));
  }
}