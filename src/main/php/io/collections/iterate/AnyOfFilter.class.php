<?php namespace io\collections\iterate;

/**
 * Combined filter that accepts an element if any of its filters
 * accept the element.
 *
 * This filter:
 * ```php
 * $filter= new AnyOfFilter([
 *   new SizeSmallerThanFilter(500),
 *   new ExtensionEqualsFilter('txt')
 * ]);
 * ```
 * will accept any elements smaller than 500 bytes or with a
 * ".txt"-extension.
 *
 * @deprecated Use util.Filters::anyOf() instead
 */
class AnyOfFilter extends AbstractCombinedFilter {
  
  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    for ($i= 0; $i < $this->_size; $i++) {
    
      // The first filter that accepts the element => we accept the element
      if ($this->list[$i]->accept($element)) return true;
    }
    
    // None of the filters have accepted the element, so we won't accept it
    return false;
  }
}
