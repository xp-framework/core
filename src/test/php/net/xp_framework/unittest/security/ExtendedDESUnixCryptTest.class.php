<?php namespace net\xp_framework\unittest\security;

use security\crypto\UnixCrypt;
use security\crypto\CryptoException;

/**
 * TestCase
 *
 * @see   xp://security.crypto.UnixCrypt
 */
class ExtendedDESUnixCryptTest extends UnixCryptTest {

  /**
   * Returns fixture
   *
   * @return  security.crypto.CryptImpl
   */
  protected function fixture() {
    return UnixCrypt::$EXTENDED;
  }

  #[@test]
  public function extendedDES() {
    $this->assertCryptedMatches('_12345678', '_12345678SkhUrQrtUJM');
  }

  #[@test]
  public function extendedDESPhpNetExample() {
    $this->assertCryptedMatches('_J9..rasm', '_J9..rasmBYk8r9AiWNc', 'rasmuslerdorf');
  }

  #[@test, @expect(CryptoException::class)]
  public function extendedDES1CharSalt() {
    $this->fixture()->crypt('plain', '_');
  }

  #[@test, @expect(CryptoException::class)]
  public function extendedDES2CharSalt() {
    $this->fixture()->crypt('plain', '_1');
  }

  #[@test, @expect(CryptoException::class)]
  public function extendedDES7CharSalt() {
    $this->fixture()->crypt('plain', '_1234567');
  }
}
