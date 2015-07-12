<?php namespace peer;

/**
 * Provide an interface to the BSD sockets
 *
 * @test  xp://net.xp_framework.unittest.peer.sockets.BSDSocketTest
 * @see   php://sockets
 * @see   http://www.developerweb.net/sock-faq/ The UNIX Socket FAQ
 * @ext   sockets
 */
class BSDSocket extends Socket {
  public
    $_eof     = false,
    $domain   = AF_INET,
    $type     = SOCK_STREAM,
    $protocol = SOL_TCP,
    $options  = [];
  
  protected 
    $rq       = '';
  
  static function __static() {
    defined('TCP_NODELAY') || define('TCP_NODELAY', 1);
  }

  /**
   * Returns local endpoint
   *
   * @return  peer.SocketEndpoint
   * @throws  peer.SocketException
   */
  public function localEndpoint() {
    if (is_resource($this->_sock)) {
      if (false === socket_getsockname($this->_sock, $host, $port)) {
        throw new SocketException('Cannot get socket name on '.$this->_sock);
      }
      return new SocketEndpoint($host, $port);
    }
    return null;    // Not connected
  }

  /**
   * Set Domain
   *
   * @param   int domain one of AF_INET or AF_UNIX
   * @throws  lang.IllegalStateException if socket is already connected
   */
  public function setDomain($domain) {
    if ($this->isConnected()) {
      throw new \lang\IllegalStateException('Cannot set domain on connected socket');
    }
    $this->domain= $domain;
  }

  /**
   * Get Domain
   *
   * @return  int
   */
  public function getDomain() {
    return $this->domain;
  }

  /**
   * Set Type
   *
   * @param   int type one of SOCK_STREAM, SOCK_DGRAM, SOCK_RAW, SOCK_SEQPACKET or SOCK_RDM
   * @throws  lang.IllegalStateException if socket is already connected
   */
  public function setType($type) {
    if ($this->isConnected()) {
      throw new \lang\IllegalStateException('Cannot set type on connected socket');
    }
    $this->type= $type;
  }

  /**
   * Get Type
   *
   * @return  int
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Set Protocol
   *
   * @see     php://getprotobyname
   * @param   int protocol one of SOL_TCP or SOL_UDP
   * @throws  lang.IllegalStateException if socket is already connected
   */
  public function setProtocol($protocol) {
    if ($this->isConnected()) {
      throw new \lang\IllegalStateException('Cannot set protocol on connected socket');
    }
    $this->protocol= $protocol;
  }

  /**
   * Get Protocol
   *
   * @return  int
   */
  public function getProtocol() {
    return $this->protocol;
  }

  /**
   * Get last error
   *
   * @return  string error
   */  
  public function getLastError() {
    return sprintf('%d: %s', $e= socket_last_error($this->_sock), socket_strerror($e));
  }
  
  /**
   * Set socket option
   *
   * @param   int level
   * @param   int name
   * @param   var value
   * @see     php://socket_set_option
   */
  public function setOption($level, $name, $value) {
    $this->options[$level][$name]= $value;

    if ($this->isConnected()) {
      socket_set_option($this->_sock, $level, $name, $value);
    }
  }
  
  /**
   * Connect
   *
   * @param   float timeout default 2.0
   * @return  bool success
   * @throws  peer.ConnectException
   */
  public function connect($timeout= 2.0) {
    static $domains= [
      AF_INET   => 'AF_INET',
      AF_INET6  => 'AF_INET6',
      AF_UNIX   => 'AF_UNIX'
    ];
    static $types= [
      SOCK_STREAM     => 'SOCK_STREAM',
      SOCK_DGRAM      => 'SOCK_DGRAM',
      SOCK_RAW        => 'SOCK_RAW',
      SOCK_SEQPACKET  => 'SOCK_SEQPACKET',
      SOCK_RDM        => 'SOCK_RDM'
    ];
    
    if ($this->isConnected()) return true;    // Short-cuircuit this
    
    // Create socket...
    if (!($this->_sock= socket_create($this->domain, $this->type, $this->protocol))) {
      $e= new ConnectException(sprintf(
        'Create of %s socket (type %s, protocol %s) failed: %d: %s',
        $domains[$this->domain],
        $types[$this->type],
        getprotobynumber($this->protocol),
        $e= socket_last_error(), 
        socket_strerror($e)
      ));
      \xp::gc(__FILE__);
      throw $e;
    }
    
    // Set options
    foreach ($this->options as $level => $pairs) {
      foreach ($pairs as $name => $value) {
        socket_set_option($this->_sock, $level, $name, $value);
      }
    }
    
    // ... and connection timeouts
    $origTimeout= $this->getTimeout();
    $this->setTimeout($timeout);

    // ...and connect it
    switch ($this->domain) {
      case AF_INET:
      case AF_INET6: {
        $host= null;
        if ($this->host instanceof \InetAddress) {
          $host= $this->host->asString();
        } else {
          // TBD: Refactor
          $host= gethostbyname($this->host);
        }
        $r= socket_connect($this->_sock, $host, $this->port);
        break;
      }
      
      case AF_UNIX: {
        $r= socket_connect($this->_sock, $this->host);
        break;
      }
    }

    // Reset initial timeout settings
    $this->setTimeout($origTimeout);

    // Check return status
    if (false === $r) {
      $e= new ConnectException(sprintf(
        'Connect to %s:%d failed: %s',
        $this->host,
        $this->port,
        $this->getLastError()
      ));
      \xp::gc(__FILE__);
      throw $e;
    }
    return true;
  }
  
  /**
   * Close socket
   *
   * @return  bool success
   */
  public function close() {
    if (!is_resource($this->_sock)) return false;

    socket_close($this->_sock);
    $this->_sock= null;
    $this->_eof= false;
    return true;
  }
  /**
   * Set timeout
   *
   * @param   var _timeout
   */
  public function setTimeout($timeout) {
    $this->_timeout= $timeout;

    // Apply changes to already opened connection
    $sec= floor($this->_timeout);
    $usec= ($this->_timeout- $sec) * 1000;
    $this->setOption(SOL_SOCKET, SO_RCVTIMEO, ['sec' => $sec, 'usec' => $usec]);
    $this->setOption(SOL_SOCKET, SO_SNDTIMEO, ['sec' => $sec, 'usec' => $usec]);
  }

  /**
   * Set socket blocking
   *
   * @param   bool blocking
   * @return  bool success
   * @throws  peer.SocketException
   */
  public function setBlocking($blocking) {
    if ($blocking) {
      $ret= socket_set_block($this->_sock);
    } else {
      $ret= socket_set_nonblock($this->_sock);
    }
    if (false === $ret) {
      $e= new SocketException(sprintf(
        'setBlocking (%s) failed: %s',
        ($blocking ? 'blocking' : 'nonblocking'),
        $this->getLastError()
      ));
      \xp::gc(__FILE__);
      throw $e;
    }
    
    return true;      
  }

  /**
   * Selection helper
   *
   * @param   var r
   * @param   var w
   * @param   var w
   * @param   float timeout
   * @return  int
   * @see     php://socket_select
   */
  protected function _select($r, $w, $e, $timeout) {
    if (null === $timeout) {
      $tv_sec= $tv_usec= null;
    } else {
      $tv_sec= (int)floor($timeout);
      $tv_usec= (int)(($timeout- $tv_sec) * 1000000);
    }

    if (false === ($n= socket_select($r, $w, $e, $tv_sec, $tv_usec))) {
      $e= new SocketException('Select failed: '.$this->getLastError());
      \xp::gc(__FILE__);
      throw $e;
    }
    return $n;
  }
      
  /**
   * Returns whether there is data that can be read
   *
   * @param   float timeout default NULL Timeout value in seconds (e.g. 0.5)
   * @return  bool there is data that can be read
   * @throws  peer.SocketException in case of failure
   */
  public function canRead($timeout= null) {
    return $this->_select([$this->_sock], null, null, $timeout) > 0;
  }
  
  /**
   * Returns whether eof has been reached
   *
   * @return  bool
   */
  public function eof() {
    return $this->_eof;
  }
  
  /**
   * Reading helper
   *
   * @param   int maxLen
   * @param   int type PHP_BINARY_READ or PHP_NORMAL_READ
   * @param   bool chop
   * @return  string data
   */
  protected function _read($maxLen, $type, $chop= false) {
    $res= '';
    if (!$this->_eof && 0 === strlen($this->rq)) {
      if (!$this->_select([$this->_sock], null, null, $this->_timeout)) {
        $e= new SocketTimeoutException('Read of '.$maxLen.' bytes failed', $this->_timeout);
        \xp::gc(__FILE__);
        throw $e;
      }
      $res= @socket_read($this->_sock, $maxLen);
      if (false === $res || null === $res) {
        $error= socket_last_error($this->_sock);
        if (0 === $error || SOCKET_ECONNRESET === $error) {
          $this->_eof= true;
          return null;
        }
        $e= new SocketException('Read of '.$maxLen.' bytes failed: '.$this->getLastError());
        \xp::gc(__FILE__);
        throw $e;
      } else if ('' === $res) {
        $this->_eof= true;
      }
    }
    
    $read= $this->rq.$res;
    if (PHP_NORMAL_READ === $type) {
      if ('' === $read) return null;
      $c= strcspn($read, "\n");
      $this->rq= substr($read, $c+ 1);
      $chunk= substr($read, 0, $c+ 1);
      return $chop ? chop($chunk) : $chunk;
    } else if (PHP_BINARY_READ === $type) {
      if ('' === $read) return '';
      $this->rq= substr($read, $maxLen);
      return substr($read, 0, $maxLen);
    }
  }
      
  /**
   * Read data from a socket
   *
   * @param   int maxLen maximum bytes to read
   * @return  string data
   * @throws  peer.SocketException
   */
  public function read($maxLen= 4096) {
    return $this->_read($maxLen, PHP_NORMAL_READ);
  }

  /**
   * Read data from a socket
   *
   * @param   int maxLen maximum bytes to read
   * @return  string data
   * @throws  peer.SocketException
   */
  public function readLine($maxLen= 4096) {
    return $this->_read($maxLen, PHP_NORMAL_READ, true);
  }
  
  /**
   * Read data from a socket (binary-safe)
   *
   * @param   int maxLen maximum bytes to read
   * @return  string data
   * @throws  peer.SocketException
   */
  public function readBinary($maxLen= 4096) {
    return $this->_read($maxLen, PHP_BINARY_READ);
  }

  /**
   * Write a string to the socket
   *
   * @param   string str
   * @return  int bytes written
   * @throws  peer.SocketException
   */
  public function write($str) {
    $len= strlen($str);
    $bytesWritten= socket_write($this->_sock, $str, $len);
    if (false === $bytesWritten || null === $bytesWritten) {
      $e= new SocketException('Write of '.$len.' bytes to socket failed: '.$this->getLastError());
      \xp::gc(__FILE__);
      throw $e;
    }
    
    return $bytesWritten;
  }
}
