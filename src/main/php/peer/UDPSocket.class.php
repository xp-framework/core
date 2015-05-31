<?php namespace peer;

/**
 * UDP (Universal Datagram Protocol) socket
 */
class UDPSocket extends Socket {

  /**
   * Constructor
   *
   * @param   string host hostname or IP address
   * @param   int port
   * @param   resource socket default NULL
   */
  public function __construct($host, $port, $socket= null) {
    parent::__construct($host, $port, $socket);
    $this->_prefix= 'udp://';
  }
}
