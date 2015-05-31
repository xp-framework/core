<?php namespace io\collections\iterate;

/**
 * Size comparison filter
 */
class SizeEqualsFilter extends AbstractSizeComparisonFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    return $element->getSize() == $this->size;
  }
}
