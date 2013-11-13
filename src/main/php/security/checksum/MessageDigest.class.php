<?php namespace security\checksum;

use security\NoSuchAlgorithmException;

/**
 * Factory class for message digests
 *
 * Creating a message digest incrementally
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 * ```php
 * $digest= MessageDigest::newInstance('md5');
 * while ($in->available() > 0) {
 *   $digest->update($in->read());
 * }
 * $md5= new MD5($digest->final());
 * ```
 *
 * Verifying
 * ~~~~~~~~~
 *
 * ```php
 * if ($md5->verify(new MD5('...'))) {
 *   // Checksums match
 * }
 * ```
 *
 * @test  xp://net.xp_framework.unittest.security.checksum.MessageDigestTest
 * @see   xp://security.checksum.DefaultDigestImpl
 */
class MessageDigest extends \lang\Object {
  protected static $implementations= array();

  static function __static() {
    \lang\XPClass::forName('security.checksum.DefaultDigestImpl');
  }

  /**
   * Register an implementation
   *
   * @param   string algorithm
   * @param   lang.XPClass<security.checksum.MessageDigestImpl> class
   * @throws  lang.IllegalArgumentException
   */
  public static function register($algorithm, \lang\XPClass $impl) {
    if (!$impl->isSubclassOf('security.checksum.MessageDigestImpl')) {
      throw new \lang\IllegalArgumentException('Implementation class must be a security.checksum.MessageDigestImpl');
    }
    self::$implementations[$algorithm]= $impl;
  }

  /**
   * Returns a list of names of supported algorithms
   *
   * @return  string[] algorithms
   */
  public static function supportedAlgorithms() {
    return array_keys(self::$implementations);
  }
  
  /**
   * Creates a new instance given an algorithm name
   *
   * @param   string algorithm
   * @return  security.checksum.MessageDigestImpl
   * @throws  security.NoSuchAlgorithmException
   */
  public static function newInstance($algorithm) {
    if (!isset(self::$implementations[$algorithm])) {
      throw new NoSuchAlgorithmException('Unsupported algorithm "'.$algorithm.'"');
    }
    return self::$implementations[$algorithm]->newInstance($algorithm);
  }
}
