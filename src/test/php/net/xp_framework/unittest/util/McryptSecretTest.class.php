<?php namespace net\xp_framework\unittest\util;

use util\Secret;
use unittest\actions\ExtensionAvailable;
use unittest\actions\RuntimeVersion;

/**
 * Testcase for mcrypt backed security.Secret implementation
 *
 * @deprecated  See https://wiki.php.net/rfc/mcrypt-viking-funeral
 */
#[@action([
#  new ExtensionAvailable('mcrypt'),
#  new RuntimeVersion('<=7.1.0')
#])]
class McryptSecretTest extends SecretTest {

  /**
   * Use MCRYPT backing
   */
  public function setUp() {
    Secret::useBacking(Secret::BACKING_MCRYPT);
  }
}
