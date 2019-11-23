<?php namespace util;

/**
 * A filter instance decides based on the passed elements whether to
 * accept them.
 */
#[@generic(['self' => 'T'])]
interface Filter {

  /**
   * Accepts a given element
   *
   * @param  T $e
   * @return bool
   */
  #[@generic(['params' => 'T'])]
  public function accept($e);
}