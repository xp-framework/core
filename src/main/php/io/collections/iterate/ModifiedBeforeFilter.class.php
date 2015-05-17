<?php namespace io\collections\iterate;

/**
 * Date comparison filter
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
