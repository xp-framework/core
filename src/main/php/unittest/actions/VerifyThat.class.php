<?php namespace unittest\actions;

use unittest\TestAction;
use unittest\TestCase;;
use unittest\PrerequisitesNotMetError;

/**
 * Verifies a certain callable works
 *
 * @test  xp://net.xp_framework.unittest.tests.VerifyThatTest
 */
class VerifyThat extends \lang\Object implements TestAction {
  protected $verify;
  protected $prerequisite;

  /**
   * Create a new verification
   *
   * @param  var callable
   */
  public function __construct($callable) {
    if ($callable instanceof \Closure) {
      $this->verify= $callable;
      $this->prerequisite= '<function()>';
    } else if (0 === strncmp($callable, 'self::', 6)) {
      $method= substr($callable, 6);
      $this->verify= function() use($method) { return call_user_func(['self', $method]); };
      $this->prerequisite= $callable;
    } else if (false !== ($p= strpos($callable, '::'))) {
      $class= \xp::reflect(substr($callable, 0, $p), '.', '\\');
      $method= substr($callable, $p+ 2);
      $this->verify= function() use($class, $method) { return call_user_func([$class, $method]); };
      $this->prerequisite= $callable;
    } else {
      $this->verify= function() use($callable) { return call_user_func([$this, $callable]); };
      $this->prerequisite= '$this->'.$callable;
    }
  }

  /**
   * This method gets invoked before a test method is invoked, and before
   * the setUp() method is called.
   *
   * @param  unittest.TestCase $t
   * @throws unittest.PrerequisitesNotMetError
   */
  public function beforeTest(TestCase $t) {
    try {
      $verified= $this->verify->bindTo($t, $t)->__invoke();
    } catch (\lang\Throwable $e) {
      throw new PrerequisitesNotMetError('Verification raised '.$e->compoundMessage(), null, [$this->prerequisite]);
    }

    if (!$verified) {
      throw new PrerequisitesNotMetError('Verification of failed', null, [$this->prerequisite]);
    }
  }

  /**
   * This method gets invoked after the test method is invoked and regard-
   * less of its outcome, after the tearDown() call has run.
   *
   * @param  unittest.TestCase $t
   */
  public function afterTest(TestCase $t) {
    // Empty
  }
}
