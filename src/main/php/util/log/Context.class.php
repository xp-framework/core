<?php namespace util\log;

/**
 * Interface to be implemented by all log contexts
 */
interface Context {

  /**
   * Creates the string representation of this log context (to be written
   * to log files)
   *
   * @return string
   */
  public function format();
}
