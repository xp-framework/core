<?php namespace peer;

/**
 * TLS socket
 *
 * @ext      openssl
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
    $this->cryptoImpl= STREAM_CRYPTO_METHOD_TLS_CLIENT;
  }
}
