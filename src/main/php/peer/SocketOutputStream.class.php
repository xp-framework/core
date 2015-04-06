<?php namespace peer;/* This file is part of the XP framework's experiments
 *
 * $Id$
 */

use io\streams\OutputStream;


/**
 * OutputStream that reads from a socket
 *
 * @purpose  OutputStream implementation
 */
class SocketOutputStream extends \lang\Object implements OutputStream {
  protected
    $socket= null;
  
  /**
   * Constructor
   *
   * @param   peer.Socket socket
   */
  public function __construct(Socket $socket) {
    $this->socket= $socket;
    $this->socket->isConnected() || $this->socket->connect();
  }


  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) {
    $this->socket->write($arg);
  }

  /**
   * Flush this buffer
   *
   */
  public function flush() {
    // NOOP, sockets cannot be flushed
  }

  /**
   * Close this buffer
   *
   */
  public function close() {
    $this->socket->isConnected() && $this->socket->close();
  }

  /**
   * Creates a string representation of this output strean
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.$this->socket->toString().'>';
  }
}
