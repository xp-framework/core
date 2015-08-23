<?php namespace net\xp_framework\unittest\security;

use security\crypto\UnixCrypt;
use security\crypto\CryptoException;

/**
 * TestCase
 *
 * @see   xp://security.crypto.UnixCrypt
 */
class BlowFishUnixCryptTest extends UnixCryptTest {

  /** @return security.crypto.CryptImpl */
  protected function fixture() { return UnixCrypt::$BLOWFISH; }

  #[@test]
  public function blowfishPhpNetExample() {
    $this->assertCryptedMatches('$2a$07$usesomesillystringforsalt$', '$2a$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi', 'rasmuslerdorf');
  }

  #[@test]
  public function blowfishSaltOneTooLong() {
    $this->assertCryptedMatches('$2a$07$usesomesillystringforsalt$X', '$2a$07$usesomesillystringforeHbE8dX9jg7DlVE.rTNXDHM0HKhUj402');
  }

  #[@test]
  public function blowfishSaltOneTooShort() {
    $this->assertCryptedMatches('$2a$07$usesomesillystringforsal', '$2a$07$usesomesillystringforeHbE8dX9jg7DlVE.rTNXDHM0HKhUj402');
  }

  #[@test]
  public function blowfishSaltDoesNotEndWithDollar() {
    $this->assertCryptedMatches('$2a$07$usesomesillystringforsalt_', '$2a$07$usesomesillystringforeHbE8dX9jg7DlVE.rTNXDHM0HKhUj402');
  }

  #[@test, @expect(CryptoException::class)]
  public function blowfishCostParameterTooShort() {
    $this->fixture()->crypt('irrelevant', '$2a$_');
  }

  #[@test, @expect(CryptoException::class)]
  public function blowfishCostParameterZero() {
    $this->fixture()->crypt('irrelevant', '$2a$00$');
  }

  #[@test, @expect(CryptoException::class)]
  public function blowfishCostParameterTooLow() {
    $this->fixture()->crypt('irrelevant', '$2a$03$');
  }

  #[@test, @expect(CryptoException::class)]
  public function blowfishCostParameterTooHigh() {
    $this->fixture()->crypt('irrelevant', '$2a$32$');
  }

  #[@test, @expect(CryptoException::class)]
  public function blowfishCostParameterMalFormed() {
    $this->fixture()->crypt('irrelevant', '$2a$__$');
  }
}
