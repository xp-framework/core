<?php namespace io\collections\iterate;



/**
 * Date comparison filter
 *
 * @purpose  Iteration Filter
 */
class CreatedBeforeFilter extends AbstractDateComparisonFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) { 
    return ($cmp= $element->createdAt()) && $cmp->isBefore($this->date);
  }
}
