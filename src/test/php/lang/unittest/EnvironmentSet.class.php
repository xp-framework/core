<?php namespace lang\unittest;

use lang\Closeable;

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