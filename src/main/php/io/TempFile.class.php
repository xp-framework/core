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
 * The temporary file is deleted when the object representing
 * it goes of out scope and is garbage-collected. To keep the
 * file, use the `persistent()` method.
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

  /**
   * Creates a new temporary file with a given prefix. Uses the environment's
   * temporary directory (typically `$TEMP`) if no other location is supplied.
   *
   * @param  string $prefix
   * @param  ?string|io.Path|io.Folder $location
   */
  public function __construct($prefix= 'tmp', $location= null) {
    if (null === $location) {
      $directory= Environment::tempDir();
    } else if ($location instanceof Folder) {
      $directory= $location->getURI();
    } else {
      $directory= (string)$location;
    }

    parent::__construct(tempnam($directory, $prefix.uniqid(microtime(true))));
  }

  /**
   * Writes the given content to this temporary file, returning this
   * for use in a fluid manner.
   *
   * @param  string $contents
   * @return self
   * @throws io.OperationFailed if an I/O error occurs
   * @throws lang.IllegalStateException if the file is open
   */
  public function containing($contents) {
    if (is_resource($this->_fd)) {
      throw new IllegalStateException('Temporary file still open');
    }

    if (false === file_put_contents($this->uri, $contents)) {
      $e= new OperationFailed('Cannot write to temporary file '.$this->uri);
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
    $this->persistent || (file_exists($this->uri) && unlink($this->uri));
  }
}
