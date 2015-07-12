<?php namespace io\collections\iterate;

/**
 * Combined filter that accepts an element if all of its filters
 * accept the element.
 *
 * This filter:
 * ```php
 * $filter= new AllOfFilter([
 *   new ModifiedBeforeFilter(new Date('Dec 14  2004')),
 *   new ExtensionEqualsFilter('jpg')
 * ]);
 * ```
 * will accept all elements modified before Dec 14  2004 AND whose
 * extension is ".jpg"
 *
 * @deprecated Use util.Filters::allOf() instead
 */
class AllOfFilter extends AbstractCombinedFilter {
  
  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    for ($i= 0; $i < $this->_size; $i++) {
    
      // The first filter that does not accept the element => we won't accept the element
      if (!$this->list[$i]->accept($element)) return false;
    }
    
    // All filters have accepted the element, so we accept it
    return true;
  }
}
