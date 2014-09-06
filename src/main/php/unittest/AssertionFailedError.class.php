<?php namespace unittest;

/**
 * Indicates an assertion failed
 *
 * @see  xp://unittest.AssertionFailedMessage
 */
class AssertionFailedError extends \lang\XPException {

  /**
   * Constructor
   *
   * @param   var message
   */
  public function __construct($message) {
    if ($message instanceof AssertionFailedMessage) {
      parent::__construct($message->format());
    } else {
      parent::__construct((string)$message);
    }
  }

  /**
   * Return compound message of this exception.
   *
   * @return  string
   */
  public function compoundMessage() {
    return $this->getClassName().'{ '.$this->message." }\n";
  }
  
  /**
   * Retrieve string representation
   *
   * @return  string
   */
  public function toString() {
    $s= $this->compoundMessage();
    
    // Slice first stack trace element, this is always unittest.TestCase::fail()
    // Also don't show the arguments
    for ($i= 1, $t= sizeof($this->trace); $i < $t; $i++) {
      $this->trace[$i]->args= null;
      $s.= $this->trace[$i]->toString();
    }
    return $s;
  }
}
