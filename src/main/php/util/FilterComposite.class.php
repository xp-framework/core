<?php namespace util;

/**
 * A filter instance decides based on the passed elements whether to
 * accept them.
 *
 * @see  xp://util.Filters
 */
class FilterComposite extends \lang\Object implements Filter {
  protected $list;
  protected $accept;

  /**
   * Constructor
   *
   * @param   util.Filter[] $list
   * @param   php.Closure $accept
   */
  public function __construct(array $list= [], \Closure $accept= null) {
    $this->list= $list;
    $this->accept= $accept;
  }

  /**
   * Adds a filter
   *
   * @param   util.Filter $filter
   * @return  util.Filter the added filter
   */
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
}