<?php namespace lang;
 
/**
 * Encapsulates the SystemException which contains an error-code
 * and the error message.
 */
class SystemException extends XPException {
  public $code= 0;
  
  /**
   * Constructor
   *
   * @param   string message the error-message
   * @param   int code the error-code
   */
  public function __construct($message, $code) {
    parent::__construct($message);
    $this->code= $code;
  }
}
