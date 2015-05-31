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

    // Omit 1st element, this is always unittest.TestCase::fail()
    array_shift($this->trace);
    foreach ($this->trace as $element) {
      $element->args= null;
    }
  }

  /**
   * Return compound message of this exception.
   *
   * @return  string
   */
  public function compoundMessage() {
    return nameof($this).'{ '.$this->message.' }';
  }
}
