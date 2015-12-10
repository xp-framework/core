<?php namespace net\xp_framework\unittest\util;

use util\Secret;
use unittest\actions\ExtensionAvailable;

/**
 * Testcase for openssl backed security.Secret implementation
 */
#[@action(new ExtensionAvailable('openssl'))]
class OpenSSLSecretTest extends SecretTest {

  /**
   * Use OPENSSL backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_OPENSSL);
  }
}
