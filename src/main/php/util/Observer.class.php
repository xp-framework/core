<?php namespace util;

/**
 * Observer interface
 *
 * @see      xp://util.Observable
 * @purpose  Interface
 */
interface Observer {

  /**
   * Update method
   *
   * @param   util.Observable obs
   * @param   var arg default NULL
   */
  public function update($obs, $arg= null);

}
