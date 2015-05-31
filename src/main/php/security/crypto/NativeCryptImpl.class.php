<?php namespace security\crypto;

/**
 * Implementation which uses PHP's crypt() function
 *
 * @see   php://crypt
 * @see   xp://security.crypto.UnixCrypt
 */
class NativeCryptImpl extends CryptImpl {

  /**
   * Crypt a given plain-text string
   *
   * @param   string plain
   * @param   string salt
   * @return  string
   */
  public function crypt($plain, $salt) {
    $crypted= crypt($plain, $salt);
    if (strlen($crypted) < 13) {      // Crypted contains error
      $message= key(@\xp::$errors[__FILE__][__LINE__ - 3]);
      \xp::gc(__FILE__);
      throw new CryptoException('Failed to crypt: '.$message);
    }
    \xp::gc(__FILE__);
    return $crypted;
  }

  /**
   * Creates a string representation of this crypt implementation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this);
  }
}
