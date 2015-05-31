<?php namespace io\collections\iterate;

/**
 * Date comparison iteration filter
 */
class AbstractDateComparisonFilter extends \lang\Object implements IterationFilter {
  public
    $date= null;
    
  /**
   * Constructor
   *
   * @param   util.Date date
   */
  public function __construct($date) {
    $this->date= $date;
  }
  
  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) { }

  /**
   * Creates a string representation of this iterator
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'('.$this->date->toString().')';
  }

} 
