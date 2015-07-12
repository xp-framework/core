<?php namespace io\collections\iterate;

/**
 * Iterates over elements of a folder, only returning those elements that
 * are accepted by the specified filter.
 *
 * ```php
 * use io\collections\FileCollection;
 * use io\collections\iterate\FilteredIOCollectionIterator;
 * use io\collections\iterate\NameMatchesFilter;
 *
 * $origin= new FileCollection('/etc');
 * foreach (new FilteredIOCollectionIterator($origin, new NameMatchesFilter('/\.jpe?g$/i')) as $file) {
 *   Console::writeLine('Element ', $file);
 * }
 * ```
 *
 * @test     xp://net.xp_framework.unittest.io.collections.IOCollectionIteratorTest
 * @see      xp://io.collections.iterate.IOCollectionIterator
 */
class FilteredIOCollectionIterator extends IOCollectionIterator {
  public
    $filter    = null;
  
  /**
   * Constructor
   *
   * @param   io.collections.IOCollection collection
   * @param   io.collections.iterate.Filter filter
   * @param   bool recursive default FALSE whether to recurse into subdirectories
   */
  public function __construct($collection, $filter, $recursive= false) {
    parent::__construct($collection, $recursive);
    $this->filter= $filter;
  }
  
  /**
   * Whether to accept a specific element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  protected function acceptElement($element) {
    return $this->filter->accept($element);
  }
  
  /**
   * Creates a string representation of this iterator
   *
   * @return  string
   */
  public function toString() {
    return parent::toString()."@{\n  ".\xp::stringOf($this->filter, '  ')."\n}";
  }
}
