<?php namespace peer\server;

use peer\ServerSocket;

/**
 * Basic TCP/IP Server
 *
 * ```php
 * use peer\server\Server;
 *   
 * $server= new Server('127.0.0.1', 6100);
 * $server->setProtocol(new MyProtocol());
 * $server->init();
 * $server->service();
 * $server->shutdown();
 * ```
 *
 * @ext   sockets
 * @see   xp://peer.ServerSocket
 * @test  xp://net.xp_framework.unittest.peer.server.ServerTest
 */
class Server extends \lang\Object {
  public
    $protocol   = null,
    $socket     = null,
    $server     = null,
    $terminate  = false,
    $tcpnodelay = false;

  /**
   * Constructor
   *
   * @param   string addr
   * @param   int port
   */
  public function __construct($addr, $port) {
    $this->socket= new ServerSocket($addr, $port);
  }
  
  /**
   * Initialize the server
   *
   */
  public function init() {
    $this->socket->create();
    $this->socket->bind(true);
    $this->socket->listen();
  }
  
  /**
   * Shutdown the server
   *
   */
  public function shutdown() {
    $this->server->terminate= true;
    $this->socket->close();
    $this->server->terminate= false;
  }
  
  /**
   * Sets this server's protocol
   *
   * @param   peer.server.ServerProtocol protocol
   * @return  peer.server.ServerProtocol protocol
   */
  public function setProtocol($protocol) {
    $protocol->server= $this;
    $this->protocol= $protocol;
    return $protocol;
  }

  /**
   * Set TCP_NODELAY
   *
   * @param   bool tcpnodelay
   */
  public function setTcpnodelay($tcpnodelay) {
    $this->tcpnodelay= $tcpnodelay;
  }

  /**
   * Get TCP_NODELAY
   *
   * @return  bool
   */
  public function getTcpnodelay() {
    return $this->tcpnodelay;
  }
  
  /**
   * Service
   *
   */
  public function service() {
    if (!$this->socket->isConnected()) return false;

    $null= null;
    $handles= $lastAction= [];
    $accepting= $this->socket->getHandle();
    $this->protocol->initialize();

    // Loop
    $tcp= getprotobyname('tcp');
    $timeout= null;
    while (!$this->terminate) {
      \xp::gc();

      // Build array of sockets that we want to check for data. If one of them
      // has disconnected in the meantime, notify the listeners (socket will be
      // already invalid at that time) and remove it from the clients list.
      $read= [$this->socket->getHandle()];
      $currentTime= time();
      foreach ($handles as $h => $handle) {
        if (!$handle->isConnected()) {
          $this->protocol->handleDisconnect($handle);
          unset($handles[$h]);
          unset($lastAction[$h]);
        } else if ($currentTime - $lastAction[$h] > $handle->getTimeout()) {
          $this->protocol->handleError($handle, new \peer\SocketTimeoutException('Timed out', $handle->getTimeout()));
          $handle->close();
          unset($handles[$h]);
          unset($lastAction[$h]);
        } else {
          $read[]= $handle->getHandle();
        }
      }

      // Check to see if there are sockets with data on it. In case we can
      // find some, loop over the returned sockets. In case the select() call
      // fails, break out of the loop and terminate the server - this really 
      // should not happen!
      do {
        $socketSelectInterrupted = false;
        if (false === socket_select($read, $null, $null, $timeout)) {
        
          // If socket_select has been interrupted by a signal, it will return FALSE,
          // but no actual error occurred - so check for "real" errors before throwing
          // an exception. If no error has occurred, skip over to the socket_select again.
          if (0 !== socket_last_error($this->socket->_sock)) {
            throw new \peer\SocketException('Call to select() failed');
          } else {
            $socketSelectInterrupted = true;
          }
        }
      // if socket_select was interrupted by signal, retry socket_select
      } while ($socketSelectInterrupted);

      foreach ($read as $i => $handle) {

        // If there is data on the server socket, this means we have a new client.
        // In case the accept() call fails, break out of the loop and terminate
        // the server - this really should not happen!
        if ($handle === $accepting) {
          if (!($m= $this->socket->accept())) {
            throw new \peer\SocketException('Call to accept() failed');
          }

          // Handle accepted socket
          if ($this->protocol instanceof \peer\server\protocol\SocketAcceptHandler) {
            if (!$this->protocol->handleAccept($m)) {
              $m->close();
              continue;
            }
          }
          
          $this->tcpnodelay && $m->setOption($tcp, TCP_NODELAY, true);
          $this->protocol->handleConnect($m);
          $index= (int)$m->getHandle();
          $handles[$index]= $m;
          $lastAction[$index]= $currentTime;
          $timeout= $m->getTimeout();
          continue;
        }
        
        // Otherwise, a client is sending data. Let the protocol decide what do
        // do with it. In case of an I/O error, close the client socket and remove 
        // the client from the list.
        $index= (int)$handle;
        $lastAction[$index]= $currentTime;
        try {
          $this->protocol->handleData($handles[$index]);
        } catch (\io\IOException $e) {
          $this->protocol->handleError($handles[$index], $e);
          $handles[$index]->close();
          unset($handles[$index]);
          unset($lastAction[$index]);
          continue;
        }
        
        // Check if we got an EOF from the client - in this file the connection
        // was gracefully closed.
        if (!$handles[$index]->isConnected() || $handles[$index]->eof()) {
          $this->protocol->handleDisconnect($handles[$index]);
          $handles[$index]->close();
          unset($handles[$index]);
          unset($lastAction[$index]);
        }
      }
    }
  }

  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<@'.$this->socket->toString().'>';
  }
}
