<?php namespace net\xp_framework\unittest\security;

use security\password\PasswordStrength;

/**
 * TestCase for PasswordStrength entry point class
 *
 * @see   xp://security.password.PasswordStrength
 */
class PasswordStrengthTest extends \unittest\TestCase {

  #[@test]
  public function standard_algorithm_is_always_available() {
    $this->assertInstanceOf(
      'security.password.StandardAlgorithm',
      PasswordStrength::getAlgorithm('standard')
    );
  }

  #[@test]
  public function register_algorithm() {
    with ($class= newinstance('security.password.Algorithm', [], '{
      public function strengthOf($password) { return 0; }
    }')->getClass()); {
      PasswordStrength::setAlgorithm('null', $class);
      $this->assertEquals($class, PasswordStrength::getAlgorithm('null')->getClass());
    }
  }

  #[@test, @expect('util.NoSuchElementException')]
  public function getAlgorithm_throws_an_exception_for_non_existant_algorithm() {
    PasswordStrength::getAlgorithm('@@NON_EXISTANT@@');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function setAlgorithm_throws_an_exception_for_non_algorithms() {
    PasswordStrength::setAlgorithm('object', $this->getClass());
  }
}
