<?php namespace io\streams;

use io\File;

/**
 * OuputStream that writes to files
 *
 * @test  xp://net.xp_framework.unittest.io.streams.FileOutputStreamTest
 */
class FileOutputStream implements OutputStream, Seekable, Truncation {
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
   * Seek to a given offset
   *
   * @param   int $offset
   * @param   int $whence default SEEK_SET (one of SEEK_[SET|CUR|END])
   * @throws  io.IOException in case of error
   */
  public function seek($offset, $whence= SEEK_SET) {
    $this->file->seek($offset, $whence);
  }

  /** @return int */
  public function tell() { return $this->file->tell(); }

  /**
   * Truncate this buffer to a given new size.
   *
   * @param  int $size
   * @return void
   */
  public function truncate($size) {
    $this->file->truncate($size);
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
