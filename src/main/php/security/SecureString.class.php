<?php namespace security;

use lang\IllegalStateException;

/**
 * SecureString provides a reasonable secure storage for security-sensistive
 * lists of characters, such as passwords.
 *
 * It prevents accidentially revealing them in output, by var_dump()ing,
 * echo()ing, or casting the object to array. All these cases will not
 * show the password, nor the crypt of it.
 *
 * However, it is not safe to consider this implementation secure in a crypto-
 * graphically sense, because it does not care for a very strong encryption,
 * and it does share the encryption key with all instances of it in a single
 * PHP instance.
 *
 * Hint: when using this class, you must make sure not to extract the secured string
 * and pass it to a place where an exception might occur, as it might be exposed as
 * method argument.
 *
 * As a rule of thumb: extract it from the container at the last possible location.
 *
 * @test  xp://net.xp_framework.unittest.security.SecureStringTest
 * @test  xp://net.xp_framework.unittest.security.McryptSecureStringTest
 * @test  xp://net.xp_framework.unittest.security.OpenSSLSecureStringTest
 * @test  xp://net.xp_framework.unittest.security.PlainTextSecureStringTest
 */
final class SecureString extends \util\Secret {

  static function __static() { }

  /**
   * Set characters to secure
   *
   * @param string $c
   */
  public function setCharacters(&$c) {
    $this->update($c);
  }

  /**
   * Retrieve secured characters
   *
   * @return string
   */
  public function getCharacters() {
    try {
      return $this->reveal();
    } catch (IllegalStateException $e) {
      throw new SecurityException($e->getMessage());
    }
  }
}