<?php namespace io\streams;/* This file is part of the XP framework's experiments
 *
 * $Id$
 */

use io\File;


/**
 * OuputStream that writes to files
 *
 * @test     xp://net.xp_framework.unittest.io.streams.FileOutputStreamTest
 * @purpose  OuputStream implementation
 */
class FileOutputStream extends \lang\Object implements OutputStream {
  protected
    $file= null;
  
  /**
   * Constructor
   *
   * @param   var file either an io.File object or a string
   * @param   bool append default FALSE whether to append
   */
  public function __construct($file, $append= false) {
    $this->file= $file instanceof File ? $file : new File($file);
    $this->file->isOpen() || $this->file->open($append ? FILE_MODE_APPEND : FILE_MODE_WRITE);
  }

  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    $this->file->write($arg);
  }

  /**
   * Flush this buffer. A NOOP for this implementation.
   *
   */
  public function flush() { 
  }

  /**
   * Creates a string representation of this file
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.$this->file->toString().'>';
  }

  /**
   * Close this buffer.
   *
   */
  public function close() {
    $this->file->isOpen() && $this->file->close();
  }
}
