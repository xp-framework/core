<?php namespace security\checksum;

/**
 * Provide an API to check SHA1 checksums
 *
 * @see   xp://security.checksum.Checksum
 * @see   php://sha1
 */
class SHA1 extends Checksum {

  /**
   * Create a new checksum from a string
   *
   * @param   string str
   * @return  security.checksum.SHA1
   */
  public static function fromString($str) {
    return new self(sha1($str));
  }

  /**
   * Returns message digest
   *
   * @return  security.checksum.MessageDigestImpl
   */
  public static function digest() {
    return MessageDigest::newInstance('sha1');
  }

  /**
   * Create a new checksum from a file object
   *
   * @param   io.File file
   * @return  security.checksum.SHA1
   */
  public static function fromFile($file) {
    return new self(sha1_file($file->uri));
  }
}
