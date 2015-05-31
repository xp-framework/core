<?php namespace io\collections\iterate;

/**
 * Abstract base class for combined filters
 *
 * @see   xp://io.collections.iterate.AnyOfFilter
 * @see   xp://io.collections.iterate.AllOfFilter
 * @deprecated  Use util.Filters instead
 */
abstract class AbstractCombinedFilter extends \lang\Object implements IterationFilter {
  public $list;
  protected $_size;
    
  /**
   * Constructor
   *
   * @param   io.collections.iterate.IterationFilter[] list
   */
  public function __construct($list= []) {
    $this->list= $list;
    $this->_size= sizeof($list);
  }
  
  /**
   * Adds a filter
   *
   * @param   io.collections.iterate.IterationFilter filter
   * @return  io.collections.iterate.IterationFilter the added filter
   */
  public function add(IterationFilter $filter) {
    $this->list[]= $filter;
    $this->_size++;
    return $filter;
  }
  
  /**
   * Creates a string representation of this iterator
   *
   * @return  string
   */
  public function toString() {
    $s= nameof($this).'('.$this->_size.")@{\n";
    for ($i= 0; $i < $this->_size; $i++) {
      $s.= '  '.\xp::stringOf($this->list[$i], '  ')."\n";
    }
    return $s.'}';
  }
} 
