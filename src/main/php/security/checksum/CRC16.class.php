<?php namespace security\checksum;
 
use io\FileUtil;

/**
 * CRC16 checksum [CRC-16 (Modbus)]
 *
 * @see   xp://security.checksum.Checksum
 * @see   http://en.wikipedia.org/wiki/Cyclic_redundancy_check
 */
class CRC16 extends Checksum {

  /**
   * Constructor
   *
   * @param   var value
   */
  public function __construct($value) {
    if (is_int($value)) {
      parent::__construct(sprintf('%04x', $value)); 
    } else {
      parent::__construct($value);
    }
  }

  /**
   * Create a new checksum from a string
   *
   * @param   string str
   * @return  self
   */
  public static function fromString($str) {
    $sum= 0xFFFF;
    for ($x= 0, $s= strlen($str); $x < $s; $x++) {
      $sum= $sum ^ ord($str{$x});
      for ($i= 0; $i < 8; $i++) {
        $sum= (1 === ($sum & 1) ? ($sum >> 1) ^ 0xA001 : $sum >> 1);
      }
    }
    return new self($sum);
  }

  /**
   * Returns message digest
   *
   * @return  security.checksum.MessageDigestImpl
   */
  public static function digest() {
    return MessageDigest::newInstance('crc16');
  }

  /**
   * Create a new checksum from a file object
   *
   * @param   io.File file
   * @return  self
   */
  public static function fromFile($file) {
    return self::fromString(FileUtil::getContents($file));
  }
}
