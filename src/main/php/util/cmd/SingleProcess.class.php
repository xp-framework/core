<?php namespace util\cmd;
 
use io\File;

/**
 * SingleProcess provides a way to insure a process is only running
 * once at a time.
 * 
 * Usage:
 * ```php
 * $sp= new SingleProcess();
 * if (!$sp->lock()) {
 *   exit(-1);
 * }
 *
 * // [...operation which should only take part once at a time...]
 *
 * $sp->unlock();
 * ```
 */  
class SingleProcess {
  public $lockfile;

  /**
   * Constructor
   *
   * @param   string lockfileName default NULL the lockfile's name,
   *          defaulting to <<program_name>>.lck
   */
  public function __construct($lockFileName= null) {
    if (null === $lockFileName) $lockFileName= $_SERVER['argv'][0].'.lck';
    $this->lockfile= new File($lockFileName);
  }
  
  /**
   * Lock this application
   *
   * @return  bool success
   */
  public function lock() {
    try {
      $this->lockfile->open(File::WRITE);
      $this->lockfile->lockExclusive();
    } catch (\io\IOException $e) {
      $this->lockfile->close();
      return false;
    }
    
    return true;
  }
  
  /**
   * Unlock the application
   *
   * @return  bool Success
   */
  public function unlock() {
    if ($this->lockfile->unlock()) {
      $this->lockfile->close();
      $this->lockfile->unlink();
      return true;
    }
    
    return false;
  }
}
