<?php namespace io;

use io\Folder;

/**
 * This class handles all neccessary file and directory operations
 * for doing reliable spool operations.
 *
 * @deprecated Extract to own library if necessary
 */    
class SpoolDirectory extends \lang\Object {
  public
    $root;
  
  public
    $_hNew=   null,
    $_hDone=  null,
    $_hTodo=  null,
    $_hError= null;

  /**
   * Creates a spooldirectory object
   *
   * @param   string root Root of the spool hierarchy
   */    
  public function __construct($root) {
    $this->root= $root;
  }

  /**
   * Opens all neccessary directorys and creates them if nonexistant
   *
   * @return  bool success
   */    
  public function open() {
    $this->_hNew=   new Folder ($this->root.DIRECTORY_SEPARATOR.'new');
    $this->_hTodo=  new Folder ($this->root.DIRECTORY_SEPARATOR.'todo');
    $this->_hDone=  new Folder ($this->root.DIRECTORY_SEPARATOR.'done');
    $this->_hError= new Folder ($this->root.DIRECTORY_SEPARATOR.'error');

    if (!$this->_hNew->exists())    $this->_hNew->create ();
    if (!$this->_hTodo->exists())   $this->_hTodo->create ();
    if (!$this->_hDone->exists())   $this->_hDone->create ();
    if (!$this->_hError->exists())  $this->_hError->create ();                  
    
    return true;
  }

  /**
   * Creates a new spool entry. The abstract is used to name the
   * file in a way that associates it with its content (e.g. a 
   * unique id). If the abstract is omitted, a id
   * generator will be used.
   *
   * @param   string abstract default NULL
   * @return  io.File opened spool file
   */    
  public function createSpoolEntry($abstract= null) {
    if (null === $abstract)
      $abstract= date ('Y-m-d-H-i-s').md5 (microtime());
    else
      $abstract= date ('Y-m-d-H-i-s').'_'.$abstract;
    
    $f= new File ($this->_hNew->getURI().DIRECTORY_SEPARATOR.$abstract.'.spool');
    $f->open (FILE_MODE_WRITE);
    return $f;
  }

  /**
   * Enqueues the spoolentry into the todo-queue. 
   *
   * @param   io.File Spoolfile
   * @return  bool success
   */    
  public function enqueueSpoolEntry($f) {
    $f->close();
    $f->move ($this->_hTodo->getURI().DIRECTORY_SEPARATOR.$f->getFileName());
    return true;
  }

  /**
   * Retrieves the next spool entry.
   *
   * @return  io.File spoolfile next spoolfile. Its opened in read/write mode.
   */    
  public function getNextSpoolEntry() {
    if (false !== ($entry= $this->_hTodo->getEntry())) {
      $f= new File ($this->_hTodo->getURI().DIRECTORY_SEPARATOR.$entry);
      $f->open (FILE_MODE_READWRITE);
    }
    
    return $f;
  }
  
  /**
   * Mark the given spool entry as done.
   *
   * @param   io.File spoolfile
   * @return  bool success
   */
  public function finishSpoolEntry($f) {
    $f->close();
    $f->move ($this->_hDone->getURI().DIRECTORY_SEPARATOR.$f->getFileName());
    
    return true;
  }

  /**
   * Mark the given spool entry as failed.
   *
   * @param   io.File spoolfile
   * @return  bool success
   */    
  public function failSpoolEntry($f) {
    $f->close();
    $f->move ($this->_hError->getURI().DIRECTORY_SEPARATOR.$f->getFileName());
    
    return true;
  }
}

