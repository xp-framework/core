<?php namespace xp\xar\instruction;

use xp\xar\Options;
use util\Filters;
use io\Path;
use lang\archive\Archive;

/**
 * Create Instruction. Always ignores well-known VCS control files!
 */
class CreateInstruction extends AbstractInstruction {
  const VERSION_CONTROL = '/^(CVS|\.svn|\.git|\.arch|\.hg|_darcs|\.bzr)$/';

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
   * Iterates through a given folder recursively and return all files
   * as io.Path instances. Filters well-known version control directories.
   *
   * @param  io.Folder $folder
   * @return php.Generator
   */
  private function filesIn($folder) {
    foreach ($folder->entries() as $entry) {
      if ($entry->isFolder() && !preg_match($entry->name(), self::VERSION_CONTROL)) {
        foreach ($this->filesIn($entry->asFolder()) as $file) {
          yield $file;
        }
      } else {
        yield $entry;
      }
    }
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
        $path= Path::real(substr($arg, 0, $p));
        $named= new Path(substr($arg, $p+ 1));
      } else {
        $path= Path::real($arg);
        $named= null;
      }

      if ($path->isFile()) {
        $this->add($named ?: $path->relativeTo($cwd), $path);
      } else if ($path->isFolder()) {
        foreach ($this->filesIn($path->asFolder()) as $source) {
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
    $this->archive->open(Archive::CREATE);
    $this->addAll($this->getArguments(), Path::real(getcwd()));
    $this->archive->create();
  }
}
