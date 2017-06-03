<?php namespace io\sys;

/**
 * Represent an IPC message
 *
 * @see   http://de3.php.net/manual/en/ref.sem.php
 */
class IPCMessage {
  public
    $message        = '',
    $type           = 0;

  /**
   * Constructor
   *
   * @param   string message
   * @param   int messagetype
   */      
  public function __construct($message, $type= 1) {
    $this->message= $message;
    $this->type= $type;
  }

  /**
   * Set Message
   *
   * @param   string message
   */
  public function setMessage($message) {
    $this->message= $message;
  }

  /**
   * Get Message
   *
   * @return  string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Set Type
   *
   * @param   var type
   */
  public function setType($type) {
    $this->type= $type;
  }

  /**
   * Get Type
   *
   * @return  var
   */
  public function getType() {
    return $this->type;
  }
}
