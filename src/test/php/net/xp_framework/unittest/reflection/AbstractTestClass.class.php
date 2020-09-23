<?php namespace net\xp_framework\unittest\reflection;

use lang\{Type, Value};

/**
 * Abstract base class
 *
 * @see      xp://net.xp_framework.unittest.reflection.ReflectionTest
 */
abstract class AbstractTestClass {
  protected
    #[Type(Value::class)]
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