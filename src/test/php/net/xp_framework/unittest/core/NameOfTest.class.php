<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;
use unittest\Assert;
use unittest\Test;

/**
 * Tests nameof() functionality
 */
class NameOfTest {

  #[Test]
  public function of_instance() {
    Assert::equals('net.xp_framework.unittest.core.NameOfTest', nameof($this));
  }

  #[Test]
  public function of_php_instance() {
    Assert::equals('Exception', nameof(new \Exception('Test')));
  }
}