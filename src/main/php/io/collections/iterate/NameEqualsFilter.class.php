<?php namespace io\collections\iterate;

/**
 * Name filter
 */
class NameEqualsFilter extends \lang\Object implements IterationFilter {
  public $compare= '';
    
  /**
   * Constructor
   *
   * @param   string compare the filename to compare to
   */
  public function __construct($compare) {
    $this->compare= $compare;
  }

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    return $this->compare === $element->getName();;
  }

  /**
   * Creates a string representation of this iterator
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'("'.$this->compare.'")';
  }
} 
