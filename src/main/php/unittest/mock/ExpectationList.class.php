<?php namespace unittest\mock;

use lang\IllegalArgumentException;
use util\collections\Vector;


/**
 * A stateful list for expectations.
 *
 */
class ExpectationList extends \lang\Object {
  private
    $list       = null,
    $called     = null,
    $unexpected = null;
  
  /**
   * Constructor      
   */
  public function __construct() {
    $this->list= new Vector();
    $this->called= new Vector();
    $this->unexpected= new Vector();
  }

  /**
   * Adds an expectation.
   * 
   * @param   unittest.mock.Expectation expectation    
   */
  public function add(Expectation $expectation) {
    $this->list->add($expectation);
  }

  /**
   * Returns the next expectation or null if no expectations left.
   *
   * @param   var[] args
   * @return  unittest.mock.Expectation  
   */
  public function getNext($args) {
    $expectation= $this->getMatching($args);
    if (null === $expectation) return null;
    
    $expectation->incActualCalls();     // increase call counter

    if (!$this->called->contains($expectation)) {
      $this->called->add($expectation);
    }
    
    if (!$expectation->canRepeat()) {   // no more repetitions left
      $idx= $this->list->indexOf($expectation);
      $this->list->remove($idx);
    }

    return $expectation;
  }

  /**
   * Returns the expectation at position $idx
   *
   * @param   int idx
   * @return  unittest.mock.Expectation  
   */
  public function getExpectation($idx) {
    return $this->list[$idx];
  }
  
  /**
   * Returns the size of the list
   *
   * @return  int  
   */
  public function size() {
    return $this->list->size();
  }
  
  /**
   * Searches for a (valid) expectation that matches the parameters
   *
   * @param   var[] args
   * @return  unittest.mock.Expectation
   */
  private function getMatching($args) {
    foreach ($this->list as $exp) {
      if ($exp->isInPropertyBehavior() || $exp->doesMatchArgs($args)) return $exp;
    }

    return null;
  }

  /**
   * Stores a call in the $unexpected list.
   *
   * @param   string method
   * @param   var[] args
   */
  public function fileUnexpected($method, $args) {
    $this->unexpected->add([$method, $args]);
  }

  /**
   * Returns the expectation list
   *
   * @return util.collections.Vector
   */
  public function getExpectations() {
    return $this->list;
  }

  /**
   * Returns expectations that have been "called"
   *
   * @return util.collections.Vector
   */
  public function getCalled() {
    return $this->called;
  }

  /**
   * Cerates a string representation
   *
   * @return string
   */
  public function toString() {
    return nameof($this).'@'.\xp::stringOf($this->list->elements());
  }
}
