<?php namespace net\xp_framework\unittest;

use lang\IllegalArgumentException;
use unittest\Assert;
use unittest\{Expect, Ignore, Limit, PrerequisitesNotMetError, Test};

/**
 * Shows different test scenarios
 */
class DemoTest {

  /**
   * Setup method
   *
   * @return void
   */
  #[Before]
  public function setUp() {
    if ('alwaysSkipped' === $this->name) {
      throw new PrerequisitesNotMetError('Skipping', null, $this->name);
    }
  }

  #[Test]
  public function alwaysSucceeds() {
    Assert::true(true);
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
    Assert::true(false);
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