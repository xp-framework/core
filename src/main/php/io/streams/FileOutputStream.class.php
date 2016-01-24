<?php namespace io\streams;

use io\File;

/**
 * OuputStream that writes to files
 *
 * @test  xp://net.xp_framework.unittest.io.streams.FileOutputStreamTest
 */
class FileOutputStream implements OutputStream {
  protected $file;
  
  /**
   * Constructor
   *
   * @param   io.File|string $file Either a file instance or a file name
   * @param   bool append default FALSE whether to append
   */
  public function __construct($file, $append= false) {
    $this->file= $file instanceof File ? $file : new File($file);
    $this->file->isOpen() || $this->file->open($append ? File::APPEND : File::WRITE);
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
   * @return void
   */
  public function close() {
    $this->file->isOpen() && $this->file->close();
  }
}
