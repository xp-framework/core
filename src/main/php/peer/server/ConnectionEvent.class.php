<?php namespace peer\server;

/**
 * Connection event
 *
 * @deprecated Implement peer.protocol.ServerProtocol instead!
 * @see      xp://peer.server.Server#service
 * @purpose  Event
 */
class ConnectionEvent extends \lang\Object {
  public
    $type     = '',
    $stream   = null,
    $data     = null;
    
  /**
   * Constructor
   *
   * @param   string type
   * @param   peer.Socket stream
   * @param   var data default NULL
   */
  public function __construct($type, $stream, $data= null) {
    $this->type= $type;
    $this->stream= $stream;
    $this->data= $data;
    
  }
}
