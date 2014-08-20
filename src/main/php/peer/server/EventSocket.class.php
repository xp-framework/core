<?php namespace peer\server;

use peer\Socket;

/**
 * Wrapper socket aroud EventBufferEvent
 *
 * @ext   event
 * @see   http://pecl.php.net/package/event
 */
class EventSocket extends \peer\Socket {
  protected $event= null;

  /**
   * Constructor
   *
   * @param  php.EventBufferEvent event
   */
  public function __construct(\EventBufferEvent $event) {
    $this->event= $event;
  }

  /**
   * Checks if EOF was reached
   *
   * @return  bool
   */
  public function eof() {
    return 0 === $this->event->input->length;
  }

  /**
   * Read data from a socket (binary-safe)
   *
   * @param   int maxLen maximum bytes to read
   * @return  string data
   * @throws  peer.SocketException
   */
  public function readBinary($maxLen= 4096) {
    return $this->event->input->read($maxLen);
  }

  /**
   * Read line from a socket
   *
   * @param   int maxLen maximum bytes to read
   * @return  string data
   * @throws  peer.SocketException
   */
  public function readLine($maxLen= 4096) {
    return $this->event->input->readLine(\EventBuffer::EOL_ANY);
  }

  /**
   * Read data from a socket
   *
   * @param   int maxLen maximum bytes to read
   * @return  string data
   * @throws  peer.SocketException
   */
  public function read($maxLen= 4096) {
    return $this->event->input->read($maxLen);
  }

  /**
   * Write a string to the socket
   *
   * @param   string str
   * @return  int bytes written
   * @throws  peer.SocketException in case of an error
   */
  public function write($data) {
    $this->event->output->add($data);
    return strlen($data);
  }

  /**
   * Close socket
   *
   * @return  bool success
   */
  public function close() {
    return false;
  }
}
