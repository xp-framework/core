<?php namespace util;

/**
 * Indicates a certain fault occurred. Service methods may throw
 * this exception to indicate a well-known, categorised exceptional
 * situation has been met.
 *
 * The faultcode set within this exception object will be propagated into
 * the server's fault message's faultcode. This code can be used by clients
 * to recognize the type of error (other than by looking at the message).
 */
class ServiceException extends \lang\XPException {
  public $faultcode;

  /**
   * Constructor
   *
   * @param   var faultcode faultcode (can be int or string)
   * @param   string message
   * @param   lang.Throwable default NULL cause causing exception
   */
  public function __construct($faultcode, $message, $cause= null) {
    $this->faultcode= $faultcode;
    parent::__construct($message, $cause);
  }

  /**
   * Set Faultcode
   *
   * @param   var faultcode
   */
  public function setFaultcode($faultcode) {
    $this->faultcode= $faultcode;
  }

  /**
   * Get Faultcode
   *
   * @return  var
   */
  public function getFaultcode() {
    return $this->faultcode;
  }
  
  /**
   * Retrieve stacktrace from cause if set or from self otherwise.
   *
   * @return  lang.StackTraceElement[] array of stack trace elements
   * @see     xp://lang.StackTraceElement
   */
  public function getStackTrace() {
    if (null !== $this->cause) return $this->cause->getStackTrace();
    
    return parent::getStackTrace();
  }
}
