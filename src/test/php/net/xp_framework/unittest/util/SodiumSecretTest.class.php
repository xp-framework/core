<?php namespace net\xp_framework\unittest\util;

use util\Secret;
use unittest\actions\ExtensionAvailable;

/**
 * Testcase for sodium backed security.Secret implementation
 */
#[@action(new ExtensionAvailable('sodium'))]
class SodiumSecretTest extends SecretTest {

  /**
   * Use Sodium backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_SODIUM);
  }
}
