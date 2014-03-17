<?php namespace unittest\actions;

/**
 * Verifies a certain callable works
 *
 * @test  xp://net.xp_framework.unittest.tests.VerifyThatTest
 */
class VerifyThat extends \lang\Object implements \unittest\TestAction {
  protected $verify;
  protected $prerequisite;

  /**
   * Create a new verification
   *
   * @param  var callable
   */
  public function __construct($callable) {
    if ($callable instanceof \Closure) {
      $this->verify= function() use($callable) {
        return call_user_func($callable->bindTo($this, $scope= $this));
      };
      $this->prerequisite= '<function()>';
    } else if (0 === strncmp($callable, 'self::', 6)) {
      $method= substr($callable, 6);
      $this->verify= function() use($method) {
        return call_user_func(['self', $method]);
      };
      $this->prerequisite= $callable;
    } else if (false !== ($p= strpos($callable, '::'))) {
      $method= \lang\XPClass::forName(substr($callable, 0, $p))->getMethod(substr($callable, $p+ 2));
      $this->verify= function() use($method) {
        return $method->invoke(null);
      };
      $this->prerequisite= $callable;
    } else {
      $this->verify= function() use($callable) {
        return call_user_func([$this, $callable]);
      };
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
  public function beforeTest(\unittest\TestCase $t) {
    $f= $this->verify->bindTo($t, $scope= $t);
    try {
      $verified= $f();
    } catch (\lang\Throwable $e) {
      throw new \unittest\PrerequisitesNotMetError('Verification raised '.$e->compoundMessage(), null, [$this->prerequisite]);
    }

    if (!$verified) {
      throw new \unittest\PrerequisitesNotMetError('Verification of failed', null, [$this->prerequisite]);
    }
  }

  /**
   * This method gets invoked after the test method is invoked and regard-
   * less of its outcome, after the tearDown() call has run.
   *
   * @param  unittest.TestCase $t
   */
  public function afterTest(\unittest\TestCase $t) {
    // Empty
  }
}
