<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;

/**
 * Tests nameof() functionality
 */
class NameOfTest extends \unittest\TestCase {

  #[@test]
  public function of_instance() {
    $this->assertEquals('net.xp_framework.unittest.core.NameOfTest', nameof($this));
  }

  #[@test]
  public function of_php_instance() {
    $this->assertEquals('Exception', nameof(new \Exception('Test')));
  }
}