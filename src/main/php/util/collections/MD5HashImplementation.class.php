<?php namespace util\collections;

/**
 * MD5
 *
 * @see   php://md5
 * @see   xp://util.collections.HashProvider
 * @test  xp://net.xp_framework.unittest.util.collections.MD5HashImplementationTest:
 */
class MD5HashImplementation extends \lang\Object implements HashImplementation {

  /**
   * Retrieve hash code for a given string
   *
   * @param   string str
   * @return  int hashcode
   */
  public function hashOf($str) {
    return '0x'.md5($str);
  }
} 
