<?php namespace io\streams;

/**
 * An OuputStream can be written to
 */
interface OutputStream extends \lang\Closeable {

  /**
   * Write a string
   *
   * @param  var $arg
   * @return void
   */
  public function write($arg);

  /**
   * Flush this buffer
   *
   * @return void
   */
  public function flush();
}
