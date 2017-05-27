<?php namespace util;

/**
 * This class adds power to an application
 *
 * @test  xp://net.xp_framework.unittest.util.BinfordTest
 * @see   http://www.binford.de/
 */
class Binford implements \lang\Value { 
  public $poweredBy= 0;

  /**
   * Constructor
   *
   * @param   int power default 6100
   * @throws  lang.IllegalArgumentException in case power contains an illegal value
   */
  public function __construct($poweredBy= 6100) {
    $this->setPoweredBy($poweredBy);
  }
  
  /**
   * Set the power
   *
   * @param   int p power
   * @throws  lang.IllegalArgumentException in case the parameter p contains an illegal value
   */
  public function setPoweredBy($p) {
    $x= log10($p / 61);
    if (floor($x) !== $x) {
      throw new \lang\IllegalArgumentException($p.' not allowed');
    }
    $this->poweredBy= $p;
  }
 
  /**
   * Retrieve the power
   *
   * @return  int power
   */
  public function getPoweredBy() {
    return $this->poweredBy;
  }
  
  /**
   * Retrieve string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'('.$this->poweredBy.')';
  }

  /**
   * Retrieve string representation of this object
   *
   * @return  string
   */
  public function hashCode() {
    return 'B'.$this->poweredBy;
  }

  /**
   * Returns whether another object is equal to this object.
   *
   * @param   var $value
   * @return  int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->poweredBy <=> $value->poweredBy : 1;
  }
}
