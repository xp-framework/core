<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'peer.ServerSocket', 
    'peer.server.ConnectionEvent',
    'peer.server.ConnectionListener'
  );

  /**
   * Basic TCP/IP Server
   *
   * <code>
   *   uses('peer.server.Server');
   *   
   *   $server= &new Server('127.0.0.1', 6100);
   *   $server->addListener(new ConnectionListener());
   *   try(); {
   *     $server->init();
   *     $server->service();
   *     $server->shutdown();
   *   } if (catch('Exception', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   * </code>
   *
   * @ext      sockets
   * @see      xp://peer.ServerSocket
   * @purpose  TCP/IP Server
   */
  class Server extends Object {
    var
      $socket   = NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string addr
     * @param   int port
     */
    function __construct($addr, $port) {
      $this->socket= &new ServerSocket($addr, $port);
      parent::__construct();
    }
    
    /**
     * Initialize the server
     *
     * @access  public
     */
    function init() {
      $this->socket->create();
      $this->socket->bind(TRUE);
      $this->socket->listen();
    }
    
    /**
     * Shutdown the server
     *
     * @access  public
     */
    function shutdown() {
      $this->socket->close();
    }
    
    /**
     * Add a connection listener
     *
     * @access  public
     * @param   &peer.server.ConnectionListener listener
     * @return  &peer.server.ConnectionListener the added listener
     */
    function &addListener(&$listener) {
      $this->listeners[]= &$listener;
      return $listener;
    }
    
    /**
     * Notify listeners
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     */
    function notify(&$event) {
      for ($i= 0, $s= sizeof($this->listeners); $i < $s; $i++) {
        $this->listeners[$i]->{$event->type}($event);
      }
    }
    
    /**
     * Service
     *
     * @access  public
     */
    function service() {
      if (!$this->socket->isConnected()) return FALSE;
      
      while ($m= &$this->socket->accept()) {
        $this->notify(new ConnectionEvent(EVENT_CONNECTED, $m));
        
        // Loop
        do {
          try(); {
            if (NULL === ($data= $m->read())) break;
          } if (catch('IOException', $e)) {
            $this->notify(new ConnectionEvent(EVENT_ERROR, $m, $e));
            break;
          }

          // Notify listeners
          $this->notify(new ConnectionEvent(EVENT_DATA, $m, $data));

        } while (!$m->eof());

        $this->notify(new ConnectionEvent(EVENT_DISCONNECTED, $m));

        // Close communications
        $m->close();
      }
    }
  }
?>
