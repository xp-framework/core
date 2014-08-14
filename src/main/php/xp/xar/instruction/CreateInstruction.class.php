<?php namespace xp\xar\instruction;

use xp\xar\Options;
use util\Filters;
use io\collections\FileCollection;
use io\collections\iterate\FilteredIOCollectionIterator;
use io\collections\iterate\UriMatchesFilter;
use io\collections\iterate\CollectionFilter;

/**
 * Create Instruction
 */
class CreateInstruction extends AbstractInstruction {

  /**
   * Add a single URI
   *
   * @param   string uri Absolute path to file
   * @param   string cwd Absolute path to current working directory
   * @param   string urn The name under which this file should be added in the archive
   */
  protected function add($uri, $cwd, $urn= null) {
    $urn || $urn= strtr(preg_replace('#^('.preg_quote($cwd, '#').'|/)#', '', $uri), DIRECTORY_SEPARATOR, '/');
    $this->options & Options::VERBOSE && $this->out->writeLine($urn);
    $this->archive->addFile($urn, new \io\File($uri));
  }

  /**
   * Retrieve files from filesystem
   *
   * @param   string cwd
   * @return  string[]
   */
  public function addAll($cwd) {
    $list= [];
    foreach ($this->getArguments() as $arg) {
      if (false !== ($p= strrpos($arg, '='))) {
        $urn= substr($arg, $p+ 1);
        $arg= substr($arg, 0, $p);
      } else {
        $urn= null;
      }
    
      if (is_file($arg)) {
        $this->add(realpath($arg), $cwd, $urn);
      } else if (is_dir($arg)) {

        // Recursively retrieve all files from directory, ignoring well-known
        // VCS control files.
        $files= new FilteredIOCollectionIterator(
          new FileCollection($arg),
          Filters::noneOf([
            new UriMatchesFilter('#/(CVS|\.svn|\.git|\.arch|\.hg|_darcs|\.bzr)/#'),
            new CollectionFilter()
          ]),
          true
        );
        foreach ($files as $file) {
          $this->add($file->getURI(), $cwd);
        }
      }
    }
    
    return $list;
  }
  
  /**
   * Execute action
   *
   * @return  int
   */
  public function perform() {
    $this->archive->open(ARCHIVE_CREATE);
    $this->addAll(rtrim(realpath(getcwd()), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
    $this->archive->create();
  }
}
