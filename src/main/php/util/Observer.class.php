<?php namespace util;

/**
 * Observer interface
 *
 * @see   util.Observable
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
