<?php namespace io\streams;/* This file is part of the XP framework's experiments
 *
 * $Id$
 */



/**
 * OuputStream that writes to memory
 *
 * @purpose  OuputStream implementation
 */
class MemoryOutputStream extends \lang\Object implements OutputStream {
  protected
    $bytes= '';
  
  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    $this->bytes.= $arg;
  }

  /**
   * Flush this buffer. A NOOP for this implementation.
   *
   */
  public function flush() { 
  }
  
  /**
   * Retrieve stored bytes
   *
   * @return  string
   */
  public function getBytes() { 
    return $this->bytes;
  }

  /**
   * Close this buffer.
   *
   * Note: Closing a memory stream has no effect!
   *
   */
  public function close() {
  }

  /**
   * Destructor.
   *
   */
  public function __destruct() {
    unset($this->bytes);
  }
}
