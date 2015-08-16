<?php namespace net\xp_framework\unittest\security;

use security\password\PasswordStrength;
use security\password\StandardAlgorithm;
use security\password\Algorithm;

/**
 * TestCase for PasswordStrength entry point class
 *
 * @see   xp://security.password.PasswordStrength
 */
class PasswordStrengthTest extends \unittest\TestCase {

  #[@test]
  public function standard_algorithm_is_always_available() {
    $this->assertInstanceOf(StandardAlgorithm::class, PasswordStrength::getAlgorithm('standard'));
  }

  #[@test]
  public function register_algorithm() {
    $algorithm= newinstance(Algorithm::class, [], [
      'strengthOf' => function($password) { return 0; }
    ]);

    PasswordStrength::setAlgorithm('test', typeof($algorithm));
    $this->assertInstanceOf(nameof($algorithm), PasswordStrength::getAlgorithm('test'));
  }

  #[@test, @expect('util.NoSuchElementException')]
  public function getAlgorithm_throws_an_exception_for_non_existant_algorithm() {
    PasswordStrength::getAlgorithm('@@NON_EXISTANT@@');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function setAlgorithm_throws_an_exception_for_non_algorithms() {
    PasswordStrength::setAlgorithm('object', typeof($this));
  }
}
