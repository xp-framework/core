<?php namespace net\xp_framework\unittest\util;

use unittest\actions\ExtensionAvailable;
use util\Secret;

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