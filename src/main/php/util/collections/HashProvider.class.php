<?php namespace util\collections;

/**
 * Provides hashing functionality for maps.
 * 
 * Basic usage:
 * 
 * ```php
 * $hashCode= HashProvider::hashOf($string);
 * ```
 *
 * Uses MD5 as default hashing implementation. To change the hashing
 * implementation to be used, use the following:
 *
 * ```php
 *   HashProvider::getInstance()->setImplementation(new MyHashImplementation());
 * ```
 * 
 * @see   xp://util.collections.DJBX33AHashImplementation
 * @see   xp://util.collections.MD5HashImplementation
 * @see   xp://util.collections.Map
 * @test  xp://net.xp_framework.unittest.util.collections.HashProviderTest
 */
class HashProvider extends \lang\Object {
  protected static
    $instance = null;

  public
    $impl     = null;

  static function __static() {
    self::$instance= new self();
    
    // Workaround for bugs in older PHP versions, see MD5HexHashImplementation's
    // class apidoc for an explanation. Earlier versions returned LONG_MAX for
    // hex numbers larger than LONG_MAX. Use 2^64 + 1 as hex literal and see if
    // it's "truncated", using the slower hexdec(md5()) implementation then.
    if (PHP_INT_MAX === @0x20c49ba5e35423 || 0 === "0x1" + 0) {
      $impl= \lang\XPClass::forName('util.collections.MD5HexHashImplementation')->newInstance();
    } else {
      $impl= new MD5HashImplementation();
    }
    self::$instance->setImplementation($impl);
  }

  /**
   * Constructor
   *
   */
  protected function __construct() {
  }
  
  /**
   * Retrieve sole instance of this object
   *
   * @return  util.collections.HashProvider
   */
  public static function getInstance() {
    return self::$instance;
  }
  
  /**
   * Returns hash for a given string
   *
   * @param   string str
   * @return  int
   */
  public static function hashOf($str) {
    return self::$instance->impl->hashOf($str);
  }

  /**
   * Set hashing implementation
   * 
   * @param   util.collections.HashImplementation impl
   * @throws  lang.IllegalArgumentException when impl is not a HashImplementation
   */
  public function setImplementation(HashImplementation $impl) {
    $this->impl= $impl;
  }

  /**
   * Get hashing implementation
   * 
   * @return  util.collections.HashImplementation
   */
  public function getImplementation() {
    return $this->impl;
  }
}
