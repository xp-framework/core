<?php namespace io;

use lang\{Environment, IllegalStateException};
  
/**
 * Represents a temporary file
 *
 * ```php
 * use io\TempFile;
 *
 * $f= new TempFile();
 * $f->open(File::WRITE);
 * $f->write('Hello');
 * $f->close();
 *
 * printf('Created temporary file "%s"', $f->getURI());
 * ```
 *
 * Note: The temporary file is not deleted when the file
 * handle is closed (e.g., a call to close()), this will have
 * to be done manually.
 *
 * Note: A possible race condition exists: From the time the
 * file name string is created (when the constructor is called)
 * until the time the file is opened (in the call to open)
 * another process may also open and/or create a file with the
 * same name. This would have to happen within the same 
 * microsecond, though, and is therefore quite unlikely.
 *
 * @see   io.File
 * @see   lang.Environment#tempDir
 * @test  io.unittest.TempFileTest
 */
class TempFile extends File {
  private $persistent= false;

  /** @param string $prefix default "tmp" */
  public function __construct($prefix= 'tmp') {
    parent::__construct(tempnam(Environment::tempDir(), $prefix.uniqid(microtime(true))));
  }

  /**
   * Writes the given content to this temporary file, returning this
   * for use in a fluid manner.
   *
   * @param  string $contents
   * @return self
   * @throws io.IOException if an I/O error occurs
   * @throws lang.IllegalStateException if the file is open
   */
  public function containing($contents) {
    if (is_resource($this->_fd)) {
      throw new IllegalStateException('Temporary file still open');
    }

    if (false === file_put_contents($this->uri, $contents)) {
      $e= new IOException('Cannot write to temporary file '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }

    return $this;
  }

  /**
   * Keeps this temporary file even after it gets garbage-collected.
   *
   * @return $this
   */
  public function persistent() {
    $this->persistent= true;
    return $this;
  }

  /** Ensures file is closed and deleted */
  public function __destruct() {
    parent::__destruct();
    $this->persistent || file_exists($this->uri) && unlink($this->uri);
  }
}
