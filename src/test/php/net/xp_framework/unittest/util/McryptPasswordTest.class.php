<?php namespace net\xp_framework\unittest\util;

use util\Password;
use unittest\actions\ExtensionAvailable;

/**
 * Testcase for mcrypt backed security.Password implementation
 */
#[@action(new ExtensionAvailable('mcrypt'))]
class McryptPasswordTest extends PasswordTest {

  /**
   * Use MCRYPT backing
   */
  public function setUp() {
    Password::useBacking(Password::BACKING_MCRYPT);
  }
}
