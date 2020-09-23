<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;
use unittest\Test;

/**
 * Tests nameof() functionality
 */
class NameOfTest extends \unittest\TestCase {

  #[Test]
  public function of_instance() {
    $this->assertEquals('net.xp_framework.unittest.core.NameOfTest', nameof($this));
  }

  #[Test]
  public function of_php_instance() {
    $this->assertEquals('Exception', nameof(new \Exception('Test')));
  }
}