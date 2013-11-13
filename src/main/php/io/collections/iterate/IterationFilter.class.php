<?php namespace io\collections\iterate;

/**
 * Iteration filter
 *
 * @see      xp://io.collections.iterate.FilteredIOCollectionIterator
 * @purpose  Interface
 */
interface IterationFilter {

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element);

}
