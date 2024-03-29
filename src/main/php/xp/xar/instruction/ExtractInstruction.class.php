<?php namespace xp\xar\instruction;

use io\{File, Files, Folder};
use lang\archive\Archive;
use xp\xar\Options;

/**
 * Extract Instruction
 */
class ExtractInstruction extends AbstractInstruction {

  /**
   * Filters entries
   *
   * @param   string entry
   * @param   string[] list
   * @return  bool
   */
  protected function _filter($entry, $list) {

    // No filters given, no filtering
    if (0 == sizeof($list)) return true;
    
    // The files to output must begin with one of the given strings...
    foreach ($list as $l) {

      // Either a directory is given
      $directory= rtrim($l, '/').'/';
      if (0 == strncmp($entry, $directory, strlen($directory))) return true;
      
      // Or a filename, but the it must match completely
      if (0 == strcmp($entry, $l)) return true;
    }
    
    return false;
  }

  /**
   * Execute action
   *
   * @return  int
   */
  public function perform() {
    $this->archive->open(Archive::READ);
    
    $args= $this->getArguments();
    while ($entry= $this->archive->getEntry()) {
      if (!$this->_filter($entry, $args)) continue;
    
      $f= new File($entry);
      $data= $this->archive->extract($entry);
      
      if (!($this->options & Options::SIMULATE)) {
      
        // Create folder on demand. Note that inside a XAR, the directory
        // separator is *ALWAYS* a forward slash, so we need to change
        // it to whatever the OS we're currently running on uses.
        $dir= new Folder(str_replace('/', DIRECTORY_SEPARATOR, dirname($entry)));
        if (!$dir->exists()) { $dir->create(); }
        
        Files::write($f, $data);
      }
      
      $this->options & Options::VERBOSE && $this->out->writeLinef('%10s %s', number_format(strlen($data), 0, false, '.'), $entry);
    }
    
    $this->archive->close();
  }
}
