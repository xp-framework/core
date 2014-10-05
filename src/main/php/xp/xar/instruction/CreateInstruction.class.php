<?php namespace xp\xar\instruction;

use xp\xar\Options;
use util\Filters;
use io\Path;
use io\collections\FileCollection;
use io\collections\iterate\FilteredIOCollectionIterator;
use io\collections\iterate\UriMatchesFilter;
use io\collections\iterate\CollectionFilter;

/**
 * Create Instruction. Always ignores well-known VCS control files!
 */
class CreateInstruction extends AbstractInstruction {
  protected static $filter;

  static function __static() {
    self::$filter= Filters::noneOf([
      new UriMatchesFilter('#/(CVS|\.svn|\.git|\.arch|\.hg|_darcs|\.bzr)/#'),
      new CollectionFilter()
    ]);
  }

  /**
   * Add a single file to the archive, and print out the name if verbose option is set.
   *
   * @param   string $name
   * @param   io.Path $path
   */
  protected function add($name, $path) {
    $this->options & Options::VERBOSE && $this->out->writeLine($name);
    $this->archive->addFile($name, $path->asFile());
  }

  /**
   * Retrieve files from filesystem
   *
   * @param   io.Path $cwd
   * @return  void
   */
  public function addAll($cwd) {
    foreach ($this->getArguments() as $arg) {
      if (false !== ($p= strrpos($arg, '='))) {
        $path= new Path(realpath(substr($arg, 0, $p)));
        $named= substr($arg, $p+ 1);
      } else {
        $path= new Path(realpath($arg));
        $named= null;
      }

      if ($path->isFile()) {
        if (null === $named) {
          $this->add($path->relativeTo($cwd)->toString('/'), $path);
        } else {
          $this->add($named, $path);
        }
      } else if ($path->isFolder()) {
        $files= new FilteredIOCollectionIterator(new FileCollection($path), self::$filter, true);
        foreach ($files as $file) {
          $target= new Path($file);
          if (null === $named) {
            $this->add($target->relativeTo($cwd)->toString('/'), $target);
          } else {
            $this->add((new Path($named, $target->relativeTo($path)))->normalize()->toString('/'), $target);
          }
        }
      }
    }
  }
  
  /**
   * Execute action
   *
   * @return  int
   */
  public function perform() {
    $this->archive->open(ARCHIVE_CREATE);
    $this->addAll(new Path(realpath(getcwd())));
    $this->archive->create();
  }
}
