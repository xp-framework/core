<?php namespace net\xp_framework\unittest\core;

use Exception;
use unittest\{Assert, Test};

class NameOfTest {

  #[Test]
  public function of_instance() {
    Assert::equals('net.xp_framework.unittest.core.NameOfTest', nameof($this));
  }

  #[Test]
  public function of_php_instance() {
    Assert::equals('Exception', nameof(new Exception('Test')));
  }
}