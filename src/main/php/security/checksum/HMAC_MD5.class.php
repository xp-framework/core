<?php namespace security\checksum;
 
use io\FileUtil;

/**
 * Provide an API to check HMAC_MD5 checksums
 *
 * @see   xp://security.checksum.Checksum
 */
class HMAC_MD5 extends Checksum {

  /**
   * Calculate HMAC_MD5 for given string (and key, if specified)
   *
   * @param   string str
   * @param   string key default NULL
   * @return  string
   */
  public static function hash($str, $key= null) {
    if (null === $key) return pack('H*', md5($str));
    
    $key= str_pad($key, 0x40, "\x00");
    if (strlen($key) > 0x40) {
      $key= pack('H*', md5($key));
    }

    $ip= $key ^ str_repeat("\x36", 0x40);
    $op= $key ^ str_repeat("\x5c", 0x40);
    
    return self::hash($op.pack('H*', md5($ip.$str)));
  }
    
  /**
   * Create a new checksum from a string
   *
   * @param   string str
   * @param   string key default NULL
   * @return  self
   */
  public static function fromString($str, $key= null) {
    return new self(self::hash($str, $key));
  }

  /**
   * Create a new checksum from a file object
   *
   * @param   io.File file
   * @param   string key default NULL
   * @return  self
   */
  public static function fromFile($file, $key= null) {
    return new self(self::hash(FileUtil::getContents($file), $key));
  }
}
