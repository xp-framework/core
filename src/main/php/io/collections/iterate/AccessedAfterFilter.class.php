<?php namespace io\collections\iterate;



/**
 * Date comparison filter
 *
 * @purpose  Iteration Filter
 */
class AccessedAfterFilter extends AbstractDateComparisonFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) { 
    return ($cmp= $element->lastAccessed()) && $cmp->isAfter($this->date);
  }
}
