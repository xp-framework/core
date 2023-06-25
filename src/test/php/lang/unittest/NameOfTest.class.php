<?php namespace lang\unittest;

use Exception;
use test\{Assert, Test};

class NameOfTest {

  #[Test]
  public function of_instance() {
    Assert::equals('lang.unittest.NameOfTest', nameof($this));
  }

  #[Test]
  public function of_php_instance() {
    Assert::equals('Exception', nameof(new Exception('Test')));
  }
}