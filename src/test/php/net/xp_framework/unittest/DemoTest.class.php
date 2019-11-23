<?php namespace net\xp_framework\unittest;

use lang\IllegalArgumentException;
use unittest\PrerequisitesNotMetError;

/**
 * Shows different test scenarios
 */
class DemoTest extends \unittest\TestCase {

  /**
   * Setup method
   *
   * @return void
   */
  public function setUp() {
    if ('alwaysSkipped' === $this->name) {
      throw new PrerequisitesNotMetError('Skipping', null, $this->name);
    }
  }

  #[@test]
  public function alwaysSucceeds() {
    $this->assertTrue(true);
  }

  #[@test, @ignore('Ignored')]
  public function ignored() {
    $this->fail('Ignored test executed', 'executed', 'ignored');
  }

  #[@test]
  public function alwaysSkipped() {
    $this->fail('Skipped test executed', 'executed', 'skipped');
  }

  #[@test]
  public function alwaysFails() {
    $this->assertTrue(false);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function expectedExceptionNotThrown() {
    // Intentionally empty
  }

  #[@test]
  public function throwsAnException() {
    throw new IllegalArgumentException('');
  }

  #[@test, @limit(['time' => 0.1])]
  public function timeouts() {
    usleep(200000);
  }
}