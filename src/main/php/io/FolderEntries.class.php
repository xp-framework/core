<?php namespace io;

use lang\IllegalArgumentException;

/**
 * Folder entries provides an iterable view of the files inside a given folder
 *
 * @test  xp://net.xp_framework.unittest.io.FolderEntriesTest
 * @see   xp://io.Folder#entries
 */
class FolderEntries extends \lang\Object implements \IteratorAggregate {
  private $base;
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

  /** @return php.Generator */
  public function getIterator() {
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

    while (false !== ($entry= readdir($this->handle))) {
      if ('.' === $entry || '..' === $entry) continue;
      yield $entry => new Path($this->base, $entry);
    }
  }

  /** @return void */
  public function __destruct() {
    if (null !== $this->handle) {
      closedir($this->handle);
      $this->handle= null;
    }
  }
}