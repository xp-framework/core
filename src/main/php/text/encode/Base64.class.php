<?php namespace text\encode;

/**
 * Encodes/decodes data with MIME base64
 *
 * <code>
 *   $b= Base64::encode($str);
 *   $str= Base64::decode($b);
 * </code>
 *
 * @see      rfc://2045#6.8
 */
class Base64 extends \lang\Object {

  /**
   * Encode string
   *
   * @param   string str
   * @return  string
   */
  public static function encode($str) { 
    return base64_encode($str);
  }
  
  /**
   * Decode base64 encoded data
   *
   * @param   string str
   * @return  string
   */
  public static function decode($str) { 
    return base64_decode($str);
  }
}
