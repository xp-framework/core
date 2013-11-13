<?php namespace peer;



/**
 * TLS socket
 *
 * @ext      openssl
 * @purpose  Specialized socket
 */
class TLSSocket extends CryptoSocket {

  /**
   * Constructor
   *
   * @param   string host hostname or IP address
   * @param   int port
   * @param   resource socket default NULL
   */
  public function __construct($host, $port, $socket= null) {
    parent::__construct($host, $port, $socket);
    $this->_prefix= 'tls://';
  }
}
