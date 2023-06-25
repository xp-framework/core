<?php namespace lang\unittest;

use lang\{Type, Value};

abstract class AbstractTestClass {
  #[Type(Value::class)]
  protected
    $inherited= null;

  /**
   * Constructor
   *
   */
  public function __construct() {}
  
  /**
   * Retrieve date
   *
   * @return  util.Date
   */    
  abstract public function getDate();

  /**
   * NOOP.
   *
   */    
  public function clearDate() {
  }
} 