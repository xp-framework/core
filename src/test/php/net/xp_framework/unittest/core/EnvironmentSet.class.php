<?php namespace net\xp_framework\unittest\core;

use lang\Closeable;

/**
 * Creates a block within which certain environment variables are set
 * to a specified value; and reset when the block is exited.
 *
 * @see  php://putenv
 */
class EnvironmentSet implements Closeable {
  private $name;
  private $original= [];

  /**
   * Use environment variables and values. Use `NULL` to indicate
   * values which should be removed from the environment.
   *
   * @param  [:string] $variables
   */
  public function __construct($variables) {
    foreach ($variables as $name => $value) {
      $this->original[$name]= getenv($name);
      if (null === $value) {
        putenv($name);
        unset($_SERVER[$name]);
      } else {
        putenv($name.'='.$value);
        $_SERVER[$name]= $value;
      }
    }
  }

  /** @return void */
  public function close() {
    foreach ($this->original as $name => $value) {
      if (false === $value) {
        putenv($name);
        unset($_SERVER[$name]);
      } else {
        putenv($name.'='.$value);
        $_SERVER[$name]= $value;
      }
    }
  }
}