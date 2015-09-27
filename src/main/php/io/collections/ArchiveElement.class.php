<?php namespace io\collections;

use lang\archive\Archive;
use io\streams\MemoryInputStream;

/**
 * Represents an element inside an archive
 *
 * @see    xp://io.collections.ArchiveCollection
 */
class ArchiveElement extends \lang\Object implements IOElement {
  protected $archive = null;
  protected $name    = '';
  protected $origin  = null;

  /**
   * Constructor
   *
   * @param   lang.archive.Archive archive
   * @param   string name
   */
  public function __construct(Archive $archive, $name) {
    $archive->isOpen() || $archive->open(Archive::READ);
    $this->archive= $archive;
    $this->name= $name;
  }

  /**
   * Returns this element's name
   *
   * @return  string
   */
  public function getName() {
    return basename($this->name);
  }

  /**
   * Returns this element's URI
   *
   * @return  string
   */
  public function getURI() { 
    return 'xar://'.$this->archive->getURI().'?'.$this->name;
  }

  /**
   * Retrieve this element's size in bytes
   *
   * @return  int
   */
  public function getSize() { 
    return $this->archive->_index[$this->name][0];
  }

  /**
   * Retrieve this element's created date and time
   *
   * @return  util.Date
   */
  public function createdAt() {
    return null;
  }

  /**
   * Retrieve this element's last-accessed date and time
   *
   * @return  util.Date
   */
  public function lastAccessed() {
    return null;
  }

  /**
   * Retrieve this element's last-modified date and time
   *
   * @return  util.Date
   */
  public function lastModified() {
    return null;
  }
  
  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() { 
    return nameof($this).'('.$this->archive->toString().'?'.$this->name.')';
  }

  /**
   * Checks whether a given element is equal to this element
   *
   * @param   lang.Generic cmp
   * @return  bool
   */
  public function equals($cmp) { 
    return $cmp instanceof self && $cmp->getURI() === $this->getURI();
  }

  /**
   * Gets origin of this element
   *
   * @return  io.collections.IOCollection
   */
  public function getOrigin() {
    return $this->origin;
  }

  /**
   * Sets origin of this element
   *
   * @param   io.collections.IOCollection
   */
  public function setOrigin(IOCollection $origin) {
    $this->origin= $origin;
  }

  /**
   * Gets input stream to read from this element
   *
   * @deprecated Use in() instead
   * @return  io.streams.InputStream
   * @throws  io.IOException
   */
  public function getInputStream() {
    return $this->in();
  }

  /**
   * Gets output stream to read from this element
   *
   * @deprecated Use out() instead
   * @return  io.streams.OutputStream
   * @throws  io.IOException
   */
  public function getOutputStream() {
    return $this->out();
  }

  /**
   * Gets input stream to read from this element
   *
   * @return  io.streams.InputStream
   * @throws  io.IOException
   */
  public function in() {
    return new MemoryInputStream($this->archive->extract($this->name));
  }

  /**
   * Gets output stream to read from this element
   *
   * @return  io.streams.OutputStream
   * @throws  io.IOException
   */
  public function out() {
    throw new \io\IOException('Cannot write to an archive');
  }
} 
