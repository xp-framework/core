<?php namespace unittest;

/**
 * Test case variation
 *
 * @see   xp://unittest.TestCase
 */
class TestVariation extends TestCase {
  protected $base= null;

  /**
   * Constructor
   *
   * @param   unittest.TestCase base
   * @param   var[] args
   */
  public function __construct($base, $args) {
    $uniq= '';
    foreach ((array)$args as $arg) {
      $uniq.= ', '.\xp::stringOf($arg);
    }
    parent::__construct($base->getName().'('.substr($uniq, 2).')');
    $this->base= $base;
  }

  /**
   * Get this test cases' name
   *
   * @param   bool compound whether to use compound format
   * @return  string
   */
  public function getName($compound= false) {
    return $compound ? nameof($this->base).'::'.$this->name : $this->name;
  }

  /**
   * Creates a string representation of this testcase
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.nameof($this->base).'::'.$this->name.'>';
  }
}
