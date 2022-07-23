<?php namespace io\streams;

use lang\Closeable;

/** An OuputStream can be written to */
interface OutputStream extends Closeable {

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
