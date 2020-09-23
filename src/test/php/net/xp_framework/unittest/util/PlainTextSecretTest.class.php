<?php namespace net\xp_framework\unittest\util;

use util\Secret;

/**
 * Testcase for plaintext backed security.Secret implementation
 */
class PlainTextSecretTest extends SecretTest {

  /**
   * Use PLAINTEXT backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_PLAINTEXT);
  }
}