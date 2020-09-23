<?php namespace net\xp_framework\unittest;

use lang\IllegalArgumentException;
use unittest\{Expect, Ignore, Limit, PrerequisitesNotMetError, Test};

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

  #[Test]
  public function alwaysSucceeds() {
    $this->assertTrue(true);
  }

  #[Test, Ignore('Ignored')]
  public function ignored() {
    $this->fail('Ignored test executed', 'executed', 'ignored');
  }

  #[Test]
  public function alwaysSkipped() {
    $this->fail('Skipped test executed', 'executed', 'skipped');
  }

  #[Test]
  public function alwaysFails() {
    $this->assertTrue(false);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function expectedExceptionNotThrown() {
    // Intentionally empty
  }

  #[Test]
  public function throwsAnException() {
    throw new IllegalArgumentException('');
  }

  #[Test, Limit(['time' => 0.1])]
  public function timeouts() {
    usleep(200000);
  }
}