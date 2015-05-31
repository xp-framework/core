<?php namespace util\log;

/**
 * Generic DB observer interface.
 */
interface BoundLogObserver extends \util\Observer {

  /**
   * Retrieves an instance.
   *
   * @param   var argument
   * @return  rdbms.DBObserver
   */
  public static function instanceFor($arg);
}
