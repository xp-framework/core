<?php namespace net\xp_framework\unittest\util;

use unittest\Action;
use unittest\actions\ExtensionAvailable;
use util\Secret;

/**
 * Testcase for sodium backed security.Secret implementation
 */
#[Action(eval: 'new ExtensionAvailable("sodium")')]
class SodiumSecretTest extends SecretTest {

  /**
   * Use Sodium backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_SODIUM);
  }
}