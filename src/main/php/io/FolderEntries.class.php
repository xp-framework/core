<?php namespace io;

use Traversable, IteratorAggregate;
use lang\IllegalArgumentException;

/**
 * Folder entries provides an iterable view of the files inside a given folder
 *
 * @test  xp://net.xp_framework.unittest.io.FolderEntriesTest
 * @see   xp://io.Folder#entries
 */
class FolderEntries implements IteratorAggregate {
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

  /** Returns a path by a given name inside this folder */
  public function named(string $name): Path {
    return new Path($this->base, $name);
  }

  /**
   * Iterate over all entries
   *
   * @throws io.NotFound
   * @throws io.OperationFailed
   */
  public function getIterator(): Traversable {
    if (null === $this->handle) {
      $uri= $this->base->asURI();
      if (!is_resource($handle= opendir($uri))) {
        $e= file_exists($uri)
          ? new OperationFailed('Cannot open folder '.$uri)
          : new NotFound('Folder '.$uri.' does not exist')
        ;
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