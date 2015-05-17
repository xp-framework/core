<?php namespace peer\server;

use lang\SystemException;

/**
 * Forking TCP/IP Server
 *
 * @ext   pcntl
 * @see   xp://peer.server.Server
 */
class ForkingServer extends Server {
  
  /**
   * Service
   *
   */
  public function service() {
    if (!$this->socket->isConnected()) return false;
    
    $tcp= getprotobyname('tcp');
    while (!$this->terminate) {
      try {
        $m= $this->socket->accept();
      } catch (\io\IOException $e) {
        $this->shutdown();
        break;
      }
      if (!$m) continue;

      // Handle accepted socket
      if ($this->protocol instanceof \peer\server\protocol\SocketAcceptHandler) {
        if (!$this->protocol->handleAccept($m)) {
          $m->close();
          continue;
        }
      }

      // Have connection, fork child
      $pid= pcntl_fork();
      if (-1 == $pid) { // Could not fork

        // If the protocol can handle this, be friendly, else simply
        // close the socket. There's not much we can do here!
        if ($this->protocol instanceof \peer\server\protocol\OutOfResourcesHandler) {
          $this->protocol->handleOutOfResources($m, new SystemException('Could not fork', -1));
        }
        $m->close();

        // Wait until one child terminates, then forking might work again
        pcntl_waitpid(-1, $status);

        // A child has joined, now continue to main loop...
        continue;
      } else if ($pid) {      // Parent

        // Close own copy of message socket
        $m->close();
        unset($m);
        
        // Use waitpid w/ NOHANG to avoid zombies hanging around
        while (pcntl_waitpid(-1, $status, WNOHANG)) { }
      } else {                // Child
        // Handle initialization of protocol. This is called once for 
        // every new child created
        $this->protocol->initialize();

        $this->tcpnodelay && $m->setOption($tcp, TCP_NODELAY, true);
        $this->protocol->handleConnect($m);

        // Loop
        do {
          try {
            $this->protocol->handleData($m);
          } catch (\io\IOException $e) {
            $this->protocol->handleError($m, $e);
            break;
          }
        } while ($m->isConnected() && !$m->eof());

        $this->protocol->handleDisconnect($m);
        $m->close();

        // Exit out of child
        exit();
      }
    }
  }
}
