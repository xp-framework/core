<?php namespace peer;

use security\cert\X509Certificate;

/**
 * Intermediate common class for all cryptographic socket classes such
 * as SSLSocket and TLSSocket.
 */
class CryptoSocket extends Socket {
  const CTX_WRP = 'ssl';      // stream context option key
  protected $crpytoImpl= null;

  /**
   * Connect, then enable crypto
   * 
   * @param   float  $timeout
   * @return  bool
   * @throws  peer.SocketException
   */
  public function connect($timeout= 2.0) {
    if ($this->isConnected()) return true;

    parent::connect($timeout);

    if (stream_socket_enable_crypto($this->_sock, true, $this->cryptoImpl)) {
      return true;
    }

    // Parse OpenSSL errors:
    if (preg_match('/error:(\d+):(.+)/', key(end(\xp::$errors[__FILE__])), $matches)) {
      switch ($matches[1]) {
        case '14090086':
          $e= new SSLUnverifiedPeerException($matches[2]); break;

        default:
          $e= new SSLHandshakeException($matches[2]); break;
      }
    } else {
      $e= new SSLHandshakeException('Unable to enable crypto.');
    }

    $this->close();
    throw $e;
  }

  /**
   * Set verify peer
   *
   * @param   bool b
   */
  public function setVerifyPeer($b) {
    $this->setSocketOption(self::CTX_WRP, 'verify_peer', $b);
  }

  /**
   * Retrieve verify peer
   *
   * @return  bool
   */
  public function getVerifyPeer() {
    return $this->getSocketOption(self::CTX_WRP, 'verify_peer');
  }

  /**
   * Set allow self signed certificates
   *
   * @param   bool b
   */
  public function setAllowSelfSigned($b) {
    $this->setSocketOption(self::CTX_WRP, 'allow_self_signed', $b);
  }

  /**
   * Retrieve allow self signed certificates
   *
   * @return  bool
   */
  public function getAllowSelfSigned() {
    return $this->getSocketOption(self::CTX_WRP, 'allow_self_signed');
  }

  /**
   * Set CA file for peer verification
   *
   * @param   string f
   */
  public function setCAFile($f) {
    $this->setSocketOption(self::CTX_WRP, 'cafile', $f);
  }

  /**
   * Retrieve CA file for peer verification
   *
   * @return  string
   */
  public function getCAFile() {
    $this->getSocketOption(self::CTX_WRP, 'cafile');
  }

  /**
   * Set CA path for peer verification
   *
   * @param   string p
   */
  public function setCAPath($p) {
    $this->setSocketOption(self::CTX_WRP, 'capath', $p);
  }

  /**
   * Retrieve CA path for peer verification
   *
   * @return  string
   */
  public function getCAPath() {
    $this->setSocketOption(self::CTX_WRP, 'capath');
  }

  /**
   * Set capture peer certificate
   *
   * @param   bool b
   */
  public function setCapturePeerCertificate($b) {
    $this->setSocketOption(self::CTX_WRP, 'capture_peer_cert', $b);
  }

  /**
   * Retrieve capture peer certificate setting
   *
   * @return  bool
   */
  public function getCapturePeerCertificate() {
    return $this->getSocketOption(self::CTX_WRP, 'capture_peer_cert');
  }

  /**
   * Set capture peer certificate chain
   *
   * @param   bool b
   */
  public function setCapturePeerCertificateChain($b) {
    $this->setSocketOption(self::CTX_WRP, 'capture_peer_cert_chain', $b);
  }

  /**
   * Retrieve capture peer certificate chain setting
   *
   * @return  bool
   */
  public function getCapturePeerCertificateChain() {
    return $this->getSocketOption(self::CTX_WRP, 'capture_peer_cert_chain');
  }

  /**
   * Retrieve captured peer certificate
   *
   * @return  security.cert.X509Certificate
   * @throws  lang.IllegalStateException if capturing is disabled
   */
  public function getPeerCertificate() {
    if (!$this->getCapturePeerCertificate()) {
      throw new \lang\IllegalStateException('Cannot get peer\'s certificate, if capturing is disabled.');
    }

    return new X509Certificate(null, $this->getSocketOption(self::CTX_WRP, 'peer_certificate'));
  }

  /**
   * Retrieve captured peer certificate chain
   *
   * @return  security.cert.X509Certificate[]
   * @throws  lang.IllegalStateException if capturing is disabled
   */
  public function getPeerCertificateChain() {
    if (!$this->getCapturePeerCertificate()) {
      throw new \lang\IllegalStateException('Cannot get peer\'s certificate chain, if capturing is disabled.');
    }

    $chain= [];
    foreach ($this->getSocketOption(self::CTX_WRP, 'peer_certificate_chain') as $cert) {
      $chain[]= new X509Certificate(null, $cert);
    }

    return $chain;
  }
}
