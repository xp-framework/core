<?php namespace net\xp_framework\unittest\core;

/**
 * Tests destructor functionality
 */
class DestructorTest extends \unittest\TestCase {
  private $destroyed= [];
  private $destroyable;
    
  /**
   * Setup method. Creates the destroyable member and sets its 
   * callback to this test.
   */
  public function setUp() {
    $this->destroyable= new Destroyable(function($object) {
      $this->destroyed[$object->hashCode()]++;
    });
    $this->destroyed[$this->destroyable->hashCode()]= 0;
  }

  #[@test]
  public function deleteCallsDestructor() {
    $hash= $this->destroyable->hashCode();
    unset($this->destroyable);
    $this->assertEquals(1, $this->destroyed[$hash]);
  } 
}
