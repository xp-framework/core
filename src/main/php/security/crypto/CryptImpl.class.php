<?php namespace security\crypto;




 
/**
 * Crypt implementation
 *
 * @see   php://crypt
 * @see   xp://security.crypto.UnixCrypt
 */
abstract class CryptImpl extends \lang\Object {
  
  /**
   * Crypt a given plain-text string
   *
   * @param   string plain
   * @param   string salt
   * @return  string
   * @throws  security.crypto.CryptoException
   */
  public abstract function crypt($plain, $salt);
  
  /**
   * Check if an entered string matches the crypt
   *
   * @param   string encrypted
   * @param   string entered
   * @return  bool
   */
  public function matches($encrypted, $entered) {
    return ($encrypted === $this->crypt($entered, $encrypted));
  }
}
