<?php namespace xp\install;

/**
 * The origin of a module
 *
 * @deprecated Use composer or glue instead
 */
interface Origin {

  /**
   * Fetches this origin into a given target folder
   *
   * @param  io.Folder $target
   */
  public function fetchInto(\io\Folder $target);
}