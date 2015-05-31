<?php namespace io\collections\iterate;

/**
 * Filter that accepts only IOCollections (e.g. directories)
 */
class CollectionFilter extends \lang\Object implements IterationFilter {
    
  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    return is('io.collections.IOCollection', $element);
  }

  /**
   * Creates a string representation of this iterator
   *
   * @return  string
   */
  public function toString() {
    return nameof($this);
  }

} 
