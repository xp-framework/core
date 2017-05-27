<?php namespace util;

/**
 * Observable - base class for Model/View/Controller architecture.
 *
 * A basic implementation might look like this:
 *
 * TextObserver class:
 * ```php
 * class TextObserver implements \util\Observer {
 *
 *   public function update($obs, $arg= NULL) {
 *     Console::writeLine(__CLASS__, ' was notified of update in value, is now ', $obs->getValue());
 *   }
 * }
 * ```
 *
 * ObservableValue class:
 * ```php
 * class ObservableValue extends \util\Observable {
 *   private $n= 0;
 *   
 *   public function __construct($n) {
 *     $this->n= $n;
 *   }
 *   
 *   public function setValue($n) {
 *     $this->n= $n;
 *     $this->setChanged();
 *     $this->notifyObservers();
 *   }
 *   
 *   public function getValue() {
 *     return $this->n;
 *   }
 * }
 * ```
 *
 * Main program:
 * ```php
 * $value= new ObservableValue(3);
 * $value->addObserver(new TextObserver());
 * $value->setValue(5);
 * ```
 *
 * The update method gets passed the instance of Observable as its first
 * argument and - if existant - the argument passed to notifyObservers as 
 * its second.
 *
 * @see   http://www.javaworld.com/javaworld/jw-10-1996/jw-10-howto.html
 * @test  xp://net.xp_framework.unittest.util.ObservableTest
 */
class Observable {
  public
    $_obs      = [],
    $_changed  = false;
    
  /**
   * Add an observer
   *
   * @param   util.Observer observer a class implementing the util.Observer interface
   * @return  util.Observer the added observer
   * @throws  lang.IllegalArgumentException in case the argument is not an observer
   */
  public function addObserver(Observer $observer) {
    $this->_obs[]= $observer;
    return $observer;
  }
  
  /**
   * Notify observers
   *
   * @param   var arg default NULL
   */
  public function notifyObservers($arg= null) {
    if (!$this->hasChanged()) return;
    
    for ($i= 0, $s= sizeof($this->_obs); $i < $s; $i++) {
      $this->_obs[$i]->update($this, $arg);
    }
    
    $this->clearChanged();
    unset($arg);
  }
  
  /**
   * Sets changed flag
   *
   */
  public function setChanged() {
    $this->_changed= true;
  }

  /**
   * Clears changed flag
   *
   */
  public function clearChanged() {
    $this->_changed= false;
  }

  /**
   * Checks whether changed flag is set
   *
   * @return  bool
   */
  public function hasChanged() {
    return $this->_changed;
  }
}
