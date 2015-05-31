<?php namespace unittest;

/**
 * Indicates prerequisites have not been met
 *
 */
class PrerequisitesNotMetError extends \lang\XPException {
  public $prerequisites= [];
    
  /**
   * Constructor
   *
   * @param   string message
   * @param   lang.Throwable cause 
   * @param   var[] prerequisites default []
   */
  public function __construct($message, \lang\Throwable $cause= null, $prerequisites= []) {
    parent::__construct($message, $cause);
    $this->prerequisites= (array)$prerequisites;
  }

  /**
   * Return compound message of this exception.
   *
   * @return  string
   */
  public function compoundMessage() {
    return sprintf(
      '%s (%s) { prerequisites: [%s] }',
      nameof($this),
      $this->message,
      implode(', ', array_map(array('xp', 'stringOf'), $this->prerequisites))
    );
  }
}
