<?php namespace io\streams;/* This file is part of the XP framework's experiments
 *
 * $Id$
 */

use lang\Closeable;


/**
 * An OuputStream can be written to
 *
 */
interface OutputStream extends Closeable {

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
