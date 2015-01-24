<?php namespace net\xp_framework\unittest;

use unittest\TestCase;
use unittest\PrerequisitesNotMetError;

/**
 * Ignores this test on HHVM
 */
class IgnoredOnHHVM extends \lang\Object implements \unittest\TestAction {

  /**
   * This method gets invoked before a test method is invoked, and before
   * the setUp() method is called.
   *
   * @param  unittest.TestCase $t
   * @return void
   * @throws unittest.PrerequisitesNotMetError
   */
  public function beforeTest(TestCase $t) {
    if (defined('HHVM_VERSION')) {
      throw new PrerequisitesNotMetError('Ignored on HHVM', null, ['!defined(HHVM_VERSION)', HHVM_VERSION]);
    }
  }

  /**
   * This method gets invoked after the test method is invoked and regard-
   * less of its outcome, after the tearDown() call has run.
   *
   * @param  unittest.TestCase $t
   * @return void
   */
  public function afterTest(TestCase $t) {
    // Empty
  }
}