<?php namespace io;

use lang\Environment;
  
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
 * @see      xp://io.File
 * @see      xp://lang.Environment#tempDir
 */
class TempFile extends File {

  /**
   * Constructor
   *
   * @param   string $prefix default "tmp"
   */
  public function __construct($prefix= 'tmp') {
    parent::__construct(tempnam(Environment::tempDir(), $prefix.uniqid(microtime(true))));
  }
}
