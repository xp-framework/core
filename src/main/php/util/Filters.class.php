<?php namespace util;

use lang\{IllegalArgumentException, IllegalStateException};

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
#[@generic(['self' => 'T', 'implements' => ['T']])]
class Filters implements Filter {
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
   * @throws  lang.IllegalArgumentException if accept is neither a closure nor NULL
   */
  #[@generic(['params' => 'util.Filter<T>[]'])]
  public function __construct(array $list= [], $accept= null) {
    $this->list= $list;
    if (null === $accept) {
      $this->accept= function($list, $e) { throw new IllegalStateException('No accepting closure set'); };
    } else if ($accept instanceof \Closure) {
      $this->accept= $accept;
    } else {
      throw new IllegalArgumentException('Expecting either a closure or null, '.typeof($accept)->getName().' given');
    }
  }

  /**
   * Adds a filter
   *
   * @param   util.Filter<T> $filter
   * @return  self<T>
   */
  #[@generic(['params' => 'util.Filter<T>', 'return' => 'self<T>'])]
  public function add($filter) {
    $this->list[]= $filter;
    return $this;
  }

  /**
   * Sets accept function
   *
   * @param   php.Closure $accept
   * @return  self<T>
   */
  public function accepting(\Closure $accept) {
    $this->accept= $accept;
    return $this;
  }

  /**
   * Accepts a given element
   *
   * @param  T $e
   * @return bool
   */
  #[@generic(['params' => 'T'])]
  public function accept($e) {
    return $this->accept->__invoke($this->list, $e);
  }

  /**
   * Creates a string representation of this instance
   *
   * @return  string
   */
  public function toString() {
    $s= nameof($this).'('.sizeof($this->list).")@{\n";
    foreach ($this->list as $filter) {
      $s.= '  '.Objects::stringOf($filter, '  ')."\n";
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