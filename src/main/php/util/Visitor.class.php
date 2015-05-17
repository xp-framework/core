<?php namespace util;

/**
 * Visitor is an interface for classes implementing the visitor pattern.
 *
 * @see       xp://util.Component
 */
interface Visitor {

  /**
   * Visits the given Component. Work on the visited objects
   * is up to implementation :)
   *
   * @param   util.Component Component
   */
  public function visit($Component);
}
