<?php namespace lang\unittest;

use test\{Assert, Before, Test};

class DestructorTest {
  private $destroyed= [];
  private $destroyable;
    
  #[Before]
  public function setUp() {
    $this->destroyable= new Destroyable(function($object) {
      $this->destroyed[$object->hashCode()]++;
    });
    $this->destroyed[$this->destroyable->hashCode()]= 0;
  }

  #[Test]
  public function deleteCallsDestructor() {
    $hash= $this->destroyable->hashCode();
    unset($this->destroyable);
    Assert::equals(1, $this->destroyed[$hash]);
  } 
}