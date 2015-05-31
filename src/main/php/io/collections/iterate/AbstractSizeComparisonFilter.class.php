<?php namespace io\collections\iterate;

/**
 * Size comparison filter
 */
class AbstractSizeComparisonFilter extends \lang\Object implements IterationFilter {
  public
    $size= 0;
    
  /**
   * Constructor
   *
   * @param   int size the size to compare to in bytes
   */
  public function __construct($size) {
    $this->size= $size;
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
    return nameof($this).'('.$this->size.')';
  }

} 
