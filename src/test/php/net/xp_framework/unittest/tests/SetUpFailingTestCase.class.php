<?php namespace net\xp_framework\unittest\tests;

use lang\IllegalArgumentException;

/**
 * TestCase for which setUp() method fails.
 */
class SetUpFailingTestCase extends \unittest\TestCase {

  /**
   * Sets up test case - throw an exception not derived from
   * unittest.PrerequisitesNotMetError or unittest.AssertionFailedError
   * which are expected.
   *
   * @return void
   */
  public function setUp() {
    throw new IllegalArgumentException('Something went wrong in setup.');
  }

  #[@test]
  public function emptyTest() {
  }
}
