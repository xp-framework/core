<?php namespace io\collections;

use io\streams\FileInputStream;
use io\streams\FileOutputStream;


/**
 * Represents a file element
 *
 * @see      xp://io.collections.FileCollection
 * @purpose  Interface
 */
class FileElement extends \lang\Object implements IOElement {
  public
    $uri= '';

  protected
    $origin = null;

  /**
   * Constructor
   *
   * @param   string uri
   */
  public function __construct($uri) {
    $this->uri= $uri;
  }

  /**
   * Returns this element's name
   *
   * @return  string
   */
  public function getName() {
    return basename($this->uri);
  }

  /**
   * Returns this element's URI
   *
   * @return  string
   */
  public function getURI() { 
    return $this->uri;
  }

  /**
   * Retrieve this element's size in bytes
   *
   * @return  int
   */
  public function getSize() { 
    return filesize($this->uri);
  }

  /**
   * Retrieve this element's created date and time
   *
   * @return  util.Date
   */
  public function createdAt() {
    return new \util\Date(filectime($this->uri));
  }

  /**
   * Retrieve this element's last-accessed date and time
   *
   * @return  util.Date
   */
  public function lastAccessed() {
    return new \util\Date(fileatime($this->uri));
  }

  /**
   * Retrieve this element's last-modified date and time
   *
   * @return  util.Date
   */
  public function lastModified() {
    return new \util\Date(filemtime($this->uri));
  }
  
  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() { 
    return $this->getClassName().'('.$this->uri.')';
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
   * @return  io.streams.InputStream
   * @throws  io.IOException
   */
  public function getInputStream() {
    return new FileInputStream($this->uri);
  }

  /**
   * Gets output stream to read from this element
   *
   * @return  io.streams.OutputStream
   * @throws  io.IOException
   */
  public function getOutputStream() {
    return new FileOutputStream($this->uri);
  }
} 
