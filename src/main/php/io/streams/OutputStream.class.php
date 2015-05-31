<?php namespace io\streams;

/**
 * An OuputStream can be written to
 */
interface OutputStream extends \lang\Closeable {

  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg);

  /**
   * Flush this buffer
   *
   */
  public function flush();
}
