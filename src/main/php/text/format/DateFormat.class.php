<?php namespace text\format;

/**
 * Date formatter
 *
 * @deprecated
 * @see      php://strftime
 * @see      xp://text.format.IFormat
 */
class DateFormat extends IFormat {

  /**
   * Get an instance
   *
   * @return  text.format.DateFormat
   */
  public function getInstance() {
    return parent::getInstance('DateFormat');
  }  

  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   * @throws  lang.FormatException
   */
  public function apply($fmt, $argument) {
    switch (gettype($argument)) {
      case 'string':
        if (-1 == ($u= strtotime($argument))) {
          throw new \lang\FormatException('Argument "'.$argument.'" cannot be converted to a date');
        }
        break;
        
      case 'integer':
      case 'float':
        $u= (int)$argument;
        break;
        
      case 'object':
        if ($argument instanceof \util\Date) {
          $u= $argument->getTime();
          break;
        }
        // Break missing intentionally
        
      default:
        throw new \lang\FormatException('Argument of type "'.gettype($argument).'" cannot be converted to a date');
    }
    
    return strftime($fmt, $u);
  }
}
