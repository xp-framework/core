<?php namespace io\collections\iterate;



/**
 * Date comparison filter
 *
 * @purpose  Iteration Filter
 */
class ModifiedBeforeFilter extends AbstractDateComparisonFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) { 
    return ($cmp= $element->lastModified()) && $cmp->isBefore($this->date);
  }
}
