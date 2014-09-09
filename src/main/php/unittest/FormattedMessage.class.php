<?php namespace unittest;

/**
 * A formatted message for an assertion failure
 *
 * @see  xp://unittest.AssertionFailedError
 */
class FormattedMessage extends \lang\Object implements AssertionFailedMessage {
  protected $format;
  protected $args;

  /**
   * Constructor
   *
   * @param   string $format
   * @param   var[] $args
   */
  public function __construct($format, $args) {
    $this->format= $format;
    $this->args= $args;
  }


  /**
   * Return formatted message
   *
   * @return  string
   */
  public function format() {
    return vsprintf($this->format, $this->args);
  }
}