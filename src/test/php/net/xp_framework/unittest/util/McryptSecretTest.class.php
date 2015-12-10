<?php namespace net\xp_framework\unittest\util;

use util\Secret;
use unittest\actions\ExtensionAvailable;

/**
 * Testcase for mcrypt backed security.Secret implementation
 */
#[@action(new ExtensionAvailable('mcrypt'))]
class McryptSecretTest extends SecretTest {

  /**
   * Use MCRYPT backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_MCRYPT);
  }
}
