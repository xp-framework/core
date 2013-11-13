<?php namespace io\collections\iterate;



/**
 * Date comparison filter
 *
 * @purpose  Iteration Filter
 */
class CreatedAfterFilter extends AbstractDateComparisonFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) { 
    return ($cmp= $element->createdAt()) && $cmp->isAfter($this->date);
  }
}
