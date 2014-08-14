<?php namespace net\xp_framework\unittest\security;

use security\crypto\UnixCrypt;

/**
 * TestCase for CVE-2012-2143. Only run these tests for PHP 5.4.4+
 *
 * @see   xp://security.crypto.UnixCrypt
 * @see   http://web.nvd.nist.gov/view/vuln/detail?vulnId=CVE-2012-2143
 * @see   http://www.php.net/archive/2012.php#id2012-06-14-1
 */
#[@action(new \unittest\actions\RuntimeVersion('>=5.4.4'))]
class CVE20122143Test extends \unittest\TestCase {

  /**
   * Assertion helper
   *
   * @param  security.crypto.CryptImpl $crypt crypt method
   * @param  string $expected
   * @param  string $plain
   * @param  string $salt
   * @throws unittest.AssertionFailedError
   */
  protected function assertCrypt($crypt, $expected, $plain, $salt) {

    // If the crypt method is not implemented, succeed, the algorithm cannot
    // be affected.
    if ($crypt instanceof \security\crypto\CryptNotImplemented) {
      $this->skip($crypt->toString().' not implemented');
    } else {
      $this->assertEquals($expected, $crypt->crypt($plain, $salt));
    }
  }

  #[@test]
  public function std_variant_1() {
    $this->assertCrypt(UnixCrypt::$STANDARD, '99PxawtsTfX56', 'À1234abcd', '99');
  }

  #[@test]
  public function std_variant_2() {
    $this->assertCrypt(UnixCrypt::$STANDARD, '99jcVcGxUZOWk', 'À9234abcd', '99');
  }

  #[@test]
  public function ext_variant_1() {
    $this->assertCrypt(UnixCrypt::$EXTENDED, '_01234567IBjxKliXXRQ', 'À1234abcd', '_01234567');
  }

  #[@test]
  public function ext_variant_2() {
    $this->assertCrypt(UnixCrypt::$EXTENDED, '_012345678OSGpGQRVHA', 'À9234abcd', '_01234567');
  }
}
