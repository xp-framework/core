<?php namespace util\cmd;

use lang\IllegalStateException;

/** Used by NoInput and NoOutput */
trait NoConsole {

  /** No-arg constructor */
  public function __construct() {
    // Intentionally empty!
  }

  /** @return void */
  public function close() {
    // Intentionally empty!
  }

  /** @return void */
  protected function raise() {
    throw new IllegalStateException('There is no console present');
  }
}