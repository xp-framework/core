<?php namespace io\collections\iterate;



/**
 * Size comparison filter
 *
 * @purpose  Iteration Filter
 */
class SizeBiggerThanFilter extends AbstractSizeComparisonFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    return $element->getSize() > $this->size;
  }
}
