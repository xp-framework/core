<?php namespace net\xp_framework\unittest\util;

use util\Password;
use unittest\actions\ExtensionAvailable;

/**
 * Testcase for openssl backed security.Password implementation
 */
#[@action(new ExtensionAvailable('openssl'))]
class OpenSSLPasswordTest extends PasswordTest {

  /**
   * Use OPENSSL backing
   */
  public function setUp() {
    Password::useBacking(Password::BACKING_OPENSSL);
  }
}
