<?php namespace net\xp_framework\unittest\core;

/**
 * Destroyable
 *
 * @see      xp://net.xp_framework.unittest.core.DestructorTest
 */
class Destroyable extends \lang\Object {
  protected $callback;

  /**
   * Creates an instance which calls the given callback when destroyed.
   *
   * @param  function(lang.Generic): void $callback
   */
  public function __construct($callback) {
    $this->callback= $callback;
  }

  /**
   * Destructor
   */
  public function __destruct() {
    $f= $this->callback;
    $f($this);
  }
}
