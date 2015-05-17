<?php namespace text\format;

/**
 * Printf formatter
 *
 * @deprecated
 * @see      php://sprintf
 * @see      xp://text.format.IFormat
 */
class PrintfFormat extends IFormat {

  /**
   * Get an instance
   *
   * @return  text.format.PrintfFormat
   */
  public function getInstance() {
    return parent::getInstance('PrintfFormat');
  }  

  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   */
  public function apply($fmt, $argument) {
    switch (gettype($argument)) {
      case 'array':
        return vsprintf($fmt, array_values($argument));

      case 'object':
        return vsprintf($fmt, array_values(get_object_vars($argument)));

      default:
        return sprintf($fmt, $argument);
    }
  }
}
