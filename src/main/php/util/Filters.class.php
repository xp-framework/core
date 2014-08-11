<?php namespace util;

/**
 * A filter instance decides based on the passed elements whether to
 * accept them.
 *
 * @see  xp://util.Filter
 * @test xp://net.xp_framework.unittest.util.FiltersTest
 */
class Filters extends \lang\Object {

  /**
   * Returns a filter which accepts elements if all of the given filter
   * instances accept it.
   *
   * @param  util.Filter<T>[] $filters
   * @param  util.Filter<T>
   */
  #[@generic(return= 'util.Filter<T>')]
  public static function allOf(array $filters) {
    return new FilterComposite($filters, function($list, $e) {
      foreach ($list as $filter) {
        if (!$filter->accept($e)) return false;
      }
      return true;
    });
  }

  /**
   * Returns a filter which accepts elements if any of the given filter
   * instances accept it.
   *
   * @param  util.Filter<T>[] $filters
   * @param  util.Filter<T>
   */
  #[@generic(return= 'util.Filter<T>')]
  public static function anyOf(array $filters) {
    return new FilterComposite($filters, function($list, $e) {
      foreach ($list as $filter) {
        if ($filter->accept($e)) return true;
      }
      return false;
    });
  }

  /**
   * Returns a filter which accepts elements if none of the given filter
   * instances accept it.
   *
   * @param  util.Filter<T>[] $filters
   * @param  util.Filter<T>
   */
  #[@generic(return= 'util.Filter<T>')]
  public static function noneOf(array $filters) {
    return new FilterComposite($filters, function($list, $e) {
      foreach ($list as $filter) {
        if ($filter->accept($e)) return false;
      }
      return true;
    });
  }
}