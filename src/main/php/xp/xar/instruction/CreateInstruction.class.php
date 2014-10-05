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
   * @param   io.Path $target Zarh in archive
   * @param   io.Path $source Path to source file
   * @return  void
   */
  protected function add($target, $source) {
    $name= $target->toString('/');
    $this->options & Options::VERBOSE && $this->out->writeLine($name);
    $this->archive->addFile($name, $source->asFile());
  }

  /**
   * Retrieve files from filesystem
   *
   * @param   string[] $arguments
   * @param   io.Path $cwd
   * @return  void
   */
  public function addAll($arguments, $cwd) {
    foreach ($arguments as $arg) {
      if (false !== ($p= strrpos($arg, '='))) {
        $path= new Path(realpath(substr($arg, 0, $p)));
        $named= new Path(substr($arg, $p+ 1));
      } else {
        $path= new Path(realpath($arg));
        $named= null;
      }

      if ($path->isFile()) {
        $this->add($named ?: $path->relativeTo($cwd), $path);
      } else if ($path->isFolder()) {
        $files= new FilteredIOCollectionIterator(new FileCollection($path), self::$filter, true);
        foreach ($files as $file) {
          $source= new Path($file);
          if ($named) {
            $this->add((new Path($named, $source->relativeTo($path)))->normalize(), $source);
          } else {
            $this->add($source->relativeTo($cwd), $source);
          }
        }
      }
    }
  }
  
  /**
   * Execute action
   *
   * @return  void
   */
  public function perform() {
    $this->archive->open(ARCHIVE_CREATE);
    $this->addAll($this->getArguments(), new Path(realpath(getcwd())));
    $this->archive->create();
  }
}
