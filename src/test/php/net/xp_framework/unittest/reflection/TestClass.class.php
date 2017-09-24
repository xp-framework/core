<?php namespace net\xp_framework\unittest\reflection;

use util\Date;
use util\collections\HashTable;
use util\collections\Vector;

/**
 * Test class
 *
 * @see    xp://net.xp_framework.unittest.reflection.ReflectionTest
 */
#[@test('Annotation')]
class TestClass extends AbstractTestClass implements \lang\Runnable {

  #[@type('util.Date')]
  public $date   = null;

  /** @var [:object] */
  public $map    = [];

  /** @type int */
  protected $size= 0;
  
  private $factor= 5;
  
  public static $initializerCalled= false;
  
  private static $cache= [];

  static function __static() {
    self::$initializerCalled= true;
  }
  
  const CONSTANT_STRING = 'XP Framework';
  const CONSTANT_INT    = 15;
  const CONSTANT_NULL   = null;

  /**
   * Constructor
   *
   * @param   var in default NULL
   */
  public function __construct($in= null) {
    $this->date= new Date($in);
  }

  /**
   * Checks whether this date is before another given test class' date
   *
   * @param   self test
   * @return  bool
   */
  public function isDateBefore(self $test) {
    return $this->date->isBefore($test->date);
  }
  
  /**
   * Returns whether static initializer was called
   *
   * @return  bool
   */
  public static function initializerCalled() {
    return self::$initializerCalled;
  }
  
  /**
   * Retrieve date
   *
   * @return  util.Date
   */    
  public function getDate() {
    return $this->date;
  }

  /**
   * Set date
   *
   * @param   util.Date date
   * @return  void
   * @throws  lang.IllegalArgumentException in case the given argument is of incorrect type
   * @throws  lang.IllegalStateException if date is before 1970
   */    
  public function setDate($date) {
    if (!$date instanceof Date) {
      throw new \lang\IllegalArgumentException('Given argument is not a util.Date');
    } else if ($date->getYear() < 1970) {
      throw new \lang\IllegalStateException('Date must be after 1970');
    }
    $this->date= $date;
  }

  public function notDocumented($param) {
  }

  /**
   * Set date
   *
   * @param   util.Date date
   * @return  self
   * @throws  lang.IllegalArgumentException in case the given argument is of incorrect type
   * @throws  lang.IllegalStateException if date is before 1970
   */
  public function withDate($date) {
    $this->setDate($date);
    return $this;
  }
  
  /**
   * Retrieve current date as UN*X timestamp
   *
   * @return  int
   */
  #[@webmethod, @security(roles= ['admin', 'god'])]
  public function currentTimestamp() {
    return time();
  }
  
  /**
   * Runnable implementation
   *
   * @return  void
   * @throws  lang.IllegalStateException *ALWAYS*
   */
  public function run() {
    throw new \lang\IllegalStateException('Not runnable yet');
  }
    
  /**
   * Retrieve map as a PHP hashmap
   *
   * @return  [:object]
   */
  public function getMap() {
    return $this->map;
  }
  
  /**
   * Clear map
   */
  protected function clearMap() {
    $this->map= [];
    return true;
  }

  /**
   * Initialite map to default values
   *
   */
  private function defaultMap() {
    $this->map= ['binford' => 61];
  }

  /**
   * Initialize map to default values
   *
   * @param   [:object] map
   */
  final public function setMap($map) {
    $this->map= $map;
  }

  /**
   * Initialize map to default values
   *
   * @param   util.collections.HashTable h
   */
  public function fromHashTable(HashTable $h) {
    // TBI
  }

  /**
   * Create a new instance statically
   *
   * @param   [:object] map
   * @return  net.xp_framework.unittest.reflection.TestClass
   */
  public static function fromMap(array $map) {
    $self= new self();
    $self->setMap($map);
    return $self;
  }
  
  /**
   * Retrieve values
   *
   * @return  util.collections.Vector<object>
   */
  public function mapValues() {
    $c= create('new Vector<object>');
    $c->addAll(array_values($this->map));
    return $c;
  }

  /**
   * Retrieve values filtered by a given pattern
   *
   * @param   string pattern default NULL
   * @return  util.collections.Vector<object>
   */
  public function filterMap($pattern= null) {
    // TBI
  }

  /**
   * Test for equality
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->date->equals($this->date);
  }
}