<?php namespace lang\unittest;

class Destroyable {
  private $id, $callback;

  /**
   * Creates an instance which calls the given callback when destroyed.
   *
   * @param  function(var): void $callback
   */
  public function __construct($callback) {
    $this->id= uniqid();
    $this->callback= $callback;
  }

  /** @return void */
  public function __destruct() {
    $f= $this->callback;
    $f($this);
  }

  /** Hashcode */
  public function hashCode() {
    return $this->id;
  }
}