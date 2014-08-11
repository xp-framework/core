<?php namespace util;

/**
 * Instances of this class act on a list of given filters, accepting
 * a closure which will be invoked with the list and the given element
 * to decide whether to accept the element.
 *
 * Furthermore, this class contains the static factory methods `allOf()`,
 * `anyOf()` and `noneOf()` to cover common cases.
 *
 * @see  xp://util.Filter
 * @test xp://net.xp_framework.unittest.util.FiltersTest
 */
#[@generic(self= 'T', implements= ['T'])]
class Filters extends \lang\Object implements Filter {
  protected $list;
  protected $accept;

  public static $ALL;
  public static $ANY;
  public static $NONE;

  static function __static() {
    self::$ALL= function($list, $e) {
      foreach ($list as $filter) {
        if (!$filter->accept($e)) return false;
      }
      return true;
    };
    self::$ANY= function($list, $e) {
      foreach ($list as $filter) {
        if ($filter->accept($e)) return true;
      }
      return false;
    };
    self::$NONE= function($list, $e) {
      foreach ($list as $filter) {
        if ($filter->accept($e)) return false;
      }
      return true;
    };
  }

  /**
   * Constructor
   *
   * @param   util.Filter<T>[] $list
   * @param   php.Closure $accept
   */
  #[@generic(params= 'util.Filter<T>[]')]
  public function __construct(array $list= array(), $accept= null) {
    $this->list= $list;
    $this->accept= $accept;
  }

  /**
   * Adds a filter
   *
   * @param   util.Filter<T> $filter
   * @return  util.Filter<T> the added filter
   */
  #[@generic(params= 'util.Filter<T>', return= 'util.Filter<T>')]
  public function add($filter) {
    $this->list[]= $filter;
    return $filter;
  }

  /**
   * Accepts a given element
   *
   * @param  T $e
   * @return bool
   */
  #[@generic(params= 'T')]
  public function accept($e) {
    return $this->accept->__invoke($this->list, $e);
  }

  /**
   * Creates a string representation of this instance
   *
   * @return  string
   */
  public function toString() {
    $s= $this->getClassName().'('.sizeof($this->list).")@{\n";
    foreach ($this->list as $filter) {
      $s.= '  '.\xp::stringOf($filter, '  ')."\n";
    }
    return $s.'}';
  }

  /**
   * Returns a filter which accepts elements if all of the given filter
   * instances accept it.
   *
   * @param  util.Filter<R>[] $filters
   * @return util.Filter<R>
   */
  public static function allOf(array $filters) {
    return new self($filters, self::$ALL);
  }

  /**
   * Returns a filter which accepts elements if any of the given filter
   * instances accept it.
   *
   * @param  util.Filter<R>[] $filters
   * @return util.Filter<R>
   */
  public static function anyOf(array $filters) {
    return new self($filters, self::$ANY);
  }

  /**
   * Returns a filter which accepts elements if none of the given filter
   * instances accept it.
   *
   * @param  util.Filter<R>[] $filters
   * @return util.Filter<R>
   */
  public static function noneOf(array $filters) {
    return new self($filters, self::$NONE);
  }
}