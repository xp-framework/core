<?php namespace net\xp_framework\unittest\util;

use util\Password;

/**
 * Testcase for plaintext backed security.Password implementation
 */
class PlainTextPasswordTest extends PasswordTest {

  /**
   * Use PLAINTEXT backing
   */
  public function setUp() {
    Password::useBacking(Password::BACKING_PLAINTEXT);
  }
}
