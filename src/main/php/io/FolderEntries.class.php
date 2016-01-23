<?php namespace io;

use lang\IllegalArgumentException;

/**
 * Folder entries provides an iterable view of the files inside a given folder
 *
 * @test  xp://net.xp_framework.unittest.io.FolderEntriesTest
 * @see   xp://io.Folder#entries
 */
class FolderEntries extends \lang\Object implements \Iterator {
  private $base, $entry;
  private $handle= null;

  /**
   * Creates a new Folder entries object
   *
   * @param  var... $args Either an io.Folder, an io.Path or a string
   * @throws lang.IllegalArgumentException
   */
  public function __construct(... $args) {
    $this->base= Path::compose($args);
    if ($this->base->isEmpty()) {
      throw new IllegalArgumentException('Cannot create from empty name');
    }
  }

  /**
   * Returns a path by a given name inside this folder
   *
   * @param  string $name
   * @return io.Path
   */
  public function named($name) {
    return new Path($this->base, $name);
  }

  /** @return string */
  public function current() { return new Path($this->base, $this->entry); }

  /** @return string */
  public function key() { return $this->entry; }

  /** @return bool */
  public function valid() { return false !== $this->entry; }

  /** @return void */
  public function next() {
    do {
      $entry= readdir($this->handle);
    } while ('.' === $entry || '..' === $entry);
    $this->entry= $entry;
  }

  /** @return void */
  public function rewind() {
    if (null === $this->handle) {
      if (!is_resource($handle= opendir($this->base->asFolder()->getURI()))) {
        $e= new IOException('Cannot open folder '.$this->base);
        \xp::gc(__FILE__);
        throw $e;
      }
      $this->handle= $handle;
    } else {
      rewinddir($this->handle);
    }
    $this->next();
  }

  /** @return void */
  public function __destruct() {
    if (null !== $this->handle) {
      closedir($this->handle);
      $this->handle= null;
    }
  }
}