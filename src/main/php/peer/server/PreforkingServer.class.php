<?php namespace peer\server;

use lang\RuntimeError;
use util\log\Traceable;

/**
 * Pre-Forking TCP/IP Server
 *
 * @ext   pcntl
 * @see   xp://peer.server.Server
 */
class PreforkingServer extends Server implements Traceable {
  public
    $cat          = null,
    $count        = 0,
    $maxrequests  = 0,
    $restart      = false,
    $null         = null;

  /**
   * Constructor
   *
   * @param   string addr
   * @param   int port
   * @param   int count default 10 number of children to fork
   * @param   int maxrequests default 1000 maxmimum # of requests per child
   */
  public function __construct($addr, $port, $count= 10, $maxrequests= 1000) {
    parent::__construct($addr, $port);
    $this->count= (int)$count;
    $this->maxrequests= (int)$maxrequests;
  }

  /**
   * Set a trace for debugging
   *
   * @param   util.log.LogCategory cat
   */
  public function setTrace($cat) {
    $this->cat= $cat;
  }

  /**
   * Terminate child processes
   *
   * @param   array children
   * @param   int signal
   */
  protected function _killChildren(&$children, $signal= SIGHUP) {
    foreach ($children as $pid => $i) {
      $this->cat && $this->cat->infof('Server #%d: Terminating child #%d, pid %d with %d', getmypid(), $i, $pid, $signal);
      posix_kill($pid, $signal);
      pcntl_signal_dispatch();

      if (SIGHUP == $signal) continue;

      pcntl_waitpid($pid, $status, WUNTRACED);
      $this->cat && $this->cat->warnf('Server #%d: Child %d died with exitcode %d', getmypid(), $pid, $status);
    }

    $this->restart= false;
  }

  /**
   * Handle a forked child
   *
   */
  public function handleChild() {
    $this->terminate= false;
    
    // Install child signal handler.
    $handler= function($sig) {
      $this->cat && $this->cat->debugf('Received signal %d in child %d', $sig, getmypid());
      $this->terminate= true;
      $this->socket->close();
    };
    pcntl_signal(SIGINT, $handler);
    pcntl_signal(SIGHUP, $handler);

    // Handle initialization of protocol. This is called once for 
    // every new child created.
    $this->protocol->initialize();
    
    $requests= 0;
    while (!$this->terminate && $requests < $this->maxrequests) {
      $read= [$this->socket->getHandle()];
      $null= null;
      $timeout= null;

      // Check to see if there are sockets with data on it. In case we can
      // find some, loop over the returned sockets. In case the select() call
      // fails, break out of the loop and terminate the server - this really
      // should not happen!
      do {
        pcntl_signal_dispatch();
        if ($this->terminate) return;

        if (false === socket_select($read, $null, $null, $timeout)) {
          pcntl_signal_dispatch();
          if ($this->terminate) return;

          // If socket_select has been interrupted by a signal, it will return FALSE,
          // but no actual error occurred - so check for "real" errors before throwing
          // an exception. If no error has occurred, skip over to the socket_select again.
          if (0 !== socket_last_error($this->socket->getHandle())) {
            throw new \peer\SocketException('Call to select() failed - ');
          }

          $accepted= false;
        } else {
          $this->socket->setBlocking(false);
          $m= $this->socket->accept();
          $this->socket->setBlocking(true);

          // The call to accept() will return false if there is no client available.
          // This is the case if select() detects activity but another child has already
          // accepted this client. In this case, retry.
          $accepted= $m instanceof \peer\Socket;
        }

      // if socket_select was interrupted by signal, retry socket_select
      } while (!$accepted);

      // Handle accepted socket
      if ($this->protocol instanceof \peer\server\protocol\SocketAcceptHandler) {
        if (!$this->protocol->handleAccept($m)) {
          $m->close();
          continue;
        }
      }
      
      $tcp= getprotobyname('tcp');
      $this->tcpnodelay && $m->setOption($tcp, TCP_NODELAY, true);
      $this->protocol->handleConnect($m);

      // Handle communication while client is connected.
      // If meanwhile the server is about to be shut
      // down, break loop and disconnect the client.
      do {
        pcntl_signal_dispatch();
        $this->cat && $this->cat->debugf('Child #%d handling data on %s...', getmypid(), $m->toString());

        try {
          $this->protocol->handleData($m);
        } catch (\io\IOException $e) {
          $this->protocol->handleError($m, $e);
          break;
        }
      } while ($m->isConnected() && !$m->eof() && !$this->terminate);

      $this->cat && $this->cat->debugf('Child #%d closing...', getmypid());

      $m->close();
      $this->protocol->handleDisconnect($m);
      $requests++;
      $this->cat && $this->cat->debug(
        'Child', getmypid(), 
        'requests=', $requests, 'max= ', $this->maxrequests
      );
      
      unset($m);
    }
  }

  /**
   * Service
   *
   * @return void
   */
  public function service() {
    if (!$this->socket->isConnected()) return false;

    $children= [];
    $i= 0;
    while (!$this->terminate && (sizeof($children) <= $this->count)) {
      $this->cat && $this->cat->debugf('Server #%d: Forking child %d', getmypid(), $i);
      $pid= pcntl_fork();
      if (-1 == $pid) {       // Woops?
        throw new RuntimeError('Could not fork');
      } else if ($pid) {      // Parent
        $this->cat && $this->cat->infof('Server #%d: Forked child #%d with pid %d', getmypid(), $i, $pid);
        $children[$pid]= $i;
        $i++;
      } else {                // Child
        $this->handleChild();
        exit();
      }
      if (sizeof($children) < $this->count) continue;

      // Set up signal handler so a kill -2 $pid (where $pid is the 
      // process id of the process we are running in) will cleanly shut
      // down this server. If this server is run within a thread (which
      // is recommended), a $thread->stop() will accomplish this.
      $terminate= function($sig) {
        $this->cat && $this->cat->debugf('Received terminate signal %d in server #%d', $sig, getmypid());
        $this->terminate= true;
      };
      pcntl_signal(SIGINT, $terminate);
      pcntl_signal(SIGTERM, $terminate);

      $restart= function($sig) {
        $this->cat && $this->cat->debugf('Received restart signal %d in server #%d', $sig, getmypid());
        $this->restart= true;
      };
      pcntl_signal(SIGHUP, $restart);
      
      // Wait until we are supposed to terminate. This condition variable
      // is set to TRUE by the signal handler. Sleep a microsecond to decrease
      // load produced. Note: usleep() is interrupted by a SIGINT, we will
      // still be able to catch the shutdown signal in realtime.
      $this->cat && $this->cat->debug('Server #'.getmypid().': Starting main loop, children:', $children);
      while (!$this->terminate) { 
        pcntl_signal_dispatch();

        // If we get SIGHUP restart child
        // processes gracefully.
        if ($this->restart) {
          $this->_killChildren($children, SIGHUP);
        }

        // If, meanwhile, we've been interrupted, break out of both loops.
        if ($this->terminate) break 2;
        
        // If one or more of our children terminated, remove them
        // from the process list and fork new ones.
        usleep(100000);
        while (($pid= pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
          $this->cat && $this->cat->warnf('Server #%d: Child %d died with exitcode %d', getmypid(), $pid, $status);
          unset($children[$pid]);
        }
        
        // Do we have to fork more children?
        if (sizeof($children) < $this->count) break;
      }
      
      // Reset signal handler so it doesn't get copied to child processes.
      pcntl_signal(SIGINT, SIG_DFL);
      pcntl_signal(SIGHUP, SIG_DFL);
    }
    
    // Send children signal to terminate.
    $this->_killChildren($children, SIGINT);
    
    // Shut down ourselves.
    $this->shutdown();
    $this->cat && $this->cat->infof('Server #%d: Shutdown complete', getmypid());
  }

  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<@'.$this->socket->toString().', children= '.$this->count.', maxrequests= '.$this->maxrequests.'>';
  }
} 
