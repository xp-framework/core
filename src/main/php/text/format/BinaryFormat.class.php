<?php namespace text\format;

/**
 * Binary data formatter
 *
 * @deprecated
 * @see      php://addcslashes
 * @see      xp://text.format.IFormat
 */
class BinaryFormat extends IFormat {

  /**
   * Get an instance
   *
   * @return  text.format.BinaryFormat
   */
  public function getInstance() {
    return parent::getInstance('BinaryFormat');
  }  

  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   */
  public function apply($fmt, $argument) {
    if (!is_scalar($argument)) {
      throw new \lang\FormatException('Argument with type '.gettype($argument).' is not scalar');
    }
    return addcslashes($argument, "\0..\37!@\@\177..\377");
  }
}
