<?php namespace util\log;

/**
 * Classes implementing the traceable interface define they can
 * be debugged by passing an util.log.LogCategory object to their
 * setTrace() method.
 */
interface Traceable {

  /**
   * Set a trace for debugging
   *
   * @param   util.log.LogCategory cat
   */
  public function setTrace($cat);
}
