<?php namespace net\xp_framework\unittest;

use unittest\TestCase;
use lang\XPClass;
use unittest\PrerequisitesNotMetError;

/**
 * Ignores this test on HHVM
 */
class IgnoredOnHHVM implements \unittest\TestAction, \unittest\TestClassAction {

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

  /**
   * This method gets invoked before any test method of the given class is
   * invoked, and before any methods annotated with beforeTest.
   *
   * @param  lang.XPClass $c
   * @return void
   * @throws unittest.PrerequisitesNotMetError
   */
  public function beforeTestClass(XPClass $c) {
    if (defined('HHVM_VERSION')) {
      throw new PrerequisitesNotMetError('Ignored on HHVM', null, ['!defined(HHVM_VERSION)', HHVM_VERSION]);
    }
  }

  /**
   * This method gets invoked after all test methods of a given class have
   * executed, and after any methods annotated with afterTest
   *
   * @param  lang.XPClass $c
   * @return void
   */
  public function afterTestClass(XPClass $c) {
    // Empty
  }
}