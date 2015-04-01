<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;

class CommentsTest extends \unittest\TestCase {
  private $fixture;

  /**
   * Initializes fixture
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }

  #[@test]
  public function for_class_without_apidoc() {
    $this->assertNull($this->getClass()->getComment());
  }

  #[@test]
  public function for_class() {
    $this->assertEquals('Test class', $this->fixture->getComment());
  }

  #[@test]
  public function for_method() {
    $this->assertEquals('Retrieve date', $this->fixture->getMethod('getDate')->getComment());
  }

  #[@test]
  public function for_method_without_apidoc() {
    $this->assertNull($this->fixture->getMethod('notDocumented')->getComment());
  }

  #[@test]
  public function for_constructor() {
    $this->assertEquals('Constructor', $this->fixture->getConstructor()->getComment());
  }

}
