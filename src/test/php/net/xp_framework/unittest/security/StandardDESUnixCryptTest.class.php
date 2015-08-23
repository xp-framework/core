<?php namespace net\xp_framework\unittest\security;

use security\crypto\UnixCrypt;
use security\crypto\CryptoException;

/**
 * TestCase
 *
 * @see   xp://security.crypto.UnixCrypt
 * @see   http://bugs.debian.org/cgi-bin/bugreport.cgi?bug=572601
 * @see   http://lxr.php.net/xref/PHP_5_3/ext/standard/crypt_freesec.c#ascii_is_unsafe
 */
class StandardDESUnixCryptTest extends UnixCryptTest {

  /**
   * Returns fixture
   *
   * @return  security.crypto.CryptImpl
   */
  protected function fixture() {
    return UnixCrypt::$STANDARD;
  }

  #[@test]
  public function traditional() {
    $this->assertCryptedMatches('ab', 'ab54209Hrroig');
  }

  #[@test]
  public function saltTooLong() {
    $this->assertCryptedMatches('abc', 'ab54209Hrroig');
  }

  #[@test]
  public function saltTooShort() {
    try {
      $this->assertCryptedMatches('a', 'a$Xz1wsurHC5M');
    } catch (CryptoException $ignored) { }
  }

  #[@test]
  public function saltWayTooLong() {
    $this->assertCryptedMatches('0123456789ABCDEF', '01f./qIYmRW1Y');
  }

  #[@test]
  public function oneDollar() {
    try {
      $this->assertCryptedMatches('1$', '1$SyvOllpoCvg');
    } catch (CryptoException $ignored) { }
  }

  #[@test]
  public function dollarTwo() {
    try {
      $this->assertCryptedMatches('$2', '$26WPvCItMuNE');
    } catch (CryptoException $ignored) { }
  }

  #[@test]
  public function dollarDollar() {
    try {
      $this->assertCryptedMatches('$$', '$$oLnFl.kOCXI');
    } catch (CryptoException $ignored) { }
  }

  #[@test, @expect(CryptoException::class)]
  public function unsafeLineFeed() {
    $this->fixture()->crypt('irrelevant', "\n_");
  }

  #[@test, @expect(CryptoException::class)]
  public function unsafeColon() {
    $this->fixture()->crypt('irrelevant', ':_');
  }
}
