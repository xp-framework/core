<?php namespace xp\xar\instruction;

use xp\xar\Options;
use io\{TempFile, File, IOException};
use lang\{Process, IllegalArgumentException};
use lang\archive\Archive;

/**
 * Shows a diff between two XARs
 */
class DiffInstruction extends AbstractInstruction {

  /**
   * Execute action
   *
   * @return  int
   */
  public function perform() {
    $this->archive->open(Archive::READ);
    
    $args= $this->getArguments();
    if (!isset($args[0]) || !file_exists(current($args)))
      throw new IllegalArgumentException('No archive to compare given or not found.');

    $cmp= new Archive(new File(current($args)));
    $cmp->open(Archive::READ);
    
    return $this->compare($this->archive, $cmp);
  }
  
  /**
   * Perform diff run
   *
   * @param   lang.archive.Archive arcl
   * @param   lang.archive.Archive arcr
   * @return  int
   */
  protected function compare($arcl, $arcr) {
    $seen= [];
    $retval= 0;
    
    while ($entry= $arcl->getEntry()) {
      $seen[$entry]= true;
      
      // Check whether second archive also has entry
      if (!$arcr->contains($entry)) {
        $this->out->writeLine($entry.' only in '.basename($arcl->file->getURI()));

        // Indicate difference
        $retval= 1;
        continue;
      }
      
      $fl= $arcl->extract($entry);
      $fr= $arcr->extract($entry);
      
      if ($fl == $fr) continue;
      
      // Indicate difference
      $retval= 1;
      $this->out->writeLine($entry.' differs.');
      
      if ($this->options & Options::VERBOSE) {
        $this->out->writeLine('=== '.$entry);
        $this->diff($fl, $fr);
      }
    }
    
    while ($entry= $arcr->getEntry()) {
      if (isset($seen[$entry])) continue;
      
      if (!$arcl->contains($entry)) {
      
        // Indicate difference
        $reval= 1;
        $this->out->writeLine($entry.' only in '.basename($arcr->file->getURI()));
      }
      
      // All other cases already handled in previous block
    }
    
    return $retval;
  }
  
  /**
   * Produce diff between the contents
   *
   * @param   string left
   * @param   string right
   */
  protected function diff($left, $right) {
    with (
      $templ= (new TempFile())->open(File::WRITE),
      $tempr= (new TempFile())->open(File::WRITE)
    ); {
      $templ->write($left);
      $tempr->write($right);
      
      $templ->close();
      $tempr->close();
      
      // TODO: Implement "diff" in userland
      try {
        $p= new Process(sprintf('diff -u %s %s', $templ->getURI(), $tempr->getURI()));
        $p->in->close();
        
        while (!$p->out->eof()) {
          $this->out->writeLine($p->out->readLine());
        }
        
        $p->close();
      } catch (IOException $e) {
        $this->err->writeLine('!=> Invocation of `diff` program failed.');
        $templ->unlink();
        $tempr->unlink();
        return;
      }
      
      $templ->unlink();
      $tempr->unlink();
    }
  }    
}
