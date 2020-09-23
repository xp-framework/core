<?php namespace util;

use lang\Generic;

/**
 * A filter instance decides based on the passed elements whether to
 * accept them.
 */
#[Generic(self: 'T')]
interface Filter {

  /**
   * Accepts a given element
   *
   * @param  T $e
   * @return bool
   */
  #[Generic(params: 'T')]
  public function accept($e);
}