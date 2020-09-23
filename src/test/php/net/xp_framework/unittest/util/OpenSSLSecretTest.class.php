<?php namespace net\xp_framework\unittest\util;

use unittest\Action;
use unittest\actions\ExtensionAvailable;
use util\Secret;

/**
 * Testcase for openssl backed security.Secret implementation
 */
#[Action(eval: 'new ExtensionAvailable("openssl")')]
class OpenSSLSecretTest extends SecretTest {

  /**
   * Use OPENSSL backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_OPENSSL);
  }
}