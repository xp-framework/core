<?php namespace util;

use lang\IllegalArgumentException;

/**
 * Composite class for util.Properties; can be used to group together
 * arbitrary many Properties objects
 *
 * @test   xp://net.xp_framework.unittest.util.CompositePropertiesTest
 */
class CompositeProperties implements PropertyAccess {
  private static $NONEXISTANT;
  protected $props  = [];

  static function __static() {

    // This is never returned from any read*() method and can help us
    // distinguish whether we read a value or not.
    self::$NONEXISTANT= function() { };
  }

  /**
   * Constructor
   *
   * @param   util.PropertyAccess[] $properties
   * @throws  lang.IllegalArgumentException if an empty array was passed
   */
  public function __construct(array $properties= []) {
    if (empty($properties)) {
      throw new IllegalArgumentException(self::class.' requires at least 1 util.PropertyAccess child.');
    }

    foreach ($properties as $p) $this->add($p);
  }

  /**
   * Add properties
   *
   * @param   util.PropertyAccess a
   */
  public function add(PropertyAccess $a) {
    foreach ($this->props as $p) {
      if ($p === $a || $p->equals($a)) return;
    }

    $this->props[]= $a;
  }

  /**
   * Retrieve number of grouped properties
   *
   * @return  int
   */
  public function length() {
    return sizeof($this->props);
  }

  /**
   * Helper method to delegate call
   *
   * @param   string method
   * @param   string section
   * @param   string key
   * @param   var default
   * @return  var
   */
  private function _read($method, $section, $key, $default) {
    foreach ($this->props as $p) {
      $value= $p->{$method}($section, $key, self::$NONEXISTANT);
      if (self::$NONEXISTANT !== $value) return $value;
    }

    return $default;
  }

  /**
   * Read string value
   *
   * @param   string section
   * @param   string key
   * @param   var default default ''
   * @return  string
   */
  public function readString($section, $key, $default= '') {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read bool value
   *
   * @param   string section
   * @param   string key
   * @param   bool default default FALSE
   * @return  bool
   */
  public function readBool($section, $key, $default= false) {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read array value
   *
   * @param   string section
   * @param   string key
   * @param   var default default []
   * @return  string[]
   */
  public function readArray($section, $key, $default= []) {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read map value
   *
   * @param   string section
   * @param   string key
   * @param   var default
   * @return  [:var]
   */
  public function readMap($section, $key, $default= null) {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read integer value
   *
   * @param   string section
   * @param   string key
   * @param   var default default 0
   * @return  int
   */
  public function readInteger($section, $key, $default= 0) {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read float value
   *
   * @param   string section
   * @param   string key
   * @param   var default default 0.0
   * @return  float
   */
  public function readFloat($section, $key, $default= 0.0) {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read range value
   *
   * @param   string section
   * @param   string key
   * @param   var default default []
   * @return  int[]
   */
  public function readRange($section, $key, $default= []) {
    return $this->_read(__FUNCTION__, $section, $key, $default);
  }

  /**
   * Read section
   *
   * @param   string section
   * @param   var default default []
   * @return  [:string]
   */
  public function readSection($section, $default= []) {
    $result= []; $sectionFound= false;
    foreach (array_reverse($this->props) as $p) {
      if (!$p->hasSection($section)) continue;
      $sectionFound= true;
      $result= array_merge($result, $p->readSection($section));
    }

    if (!$sectionFound) return $default;
    return $result;
  }

  /**
   * Test whether a given section exists
   *
   * @param   string section
   * @return  bool
   */
  public function hasSection($section) {
    foreach ($this->props as $p) {
      if (true === $p->hasSection($section)) return true;
    }

    return false;
  }

  /** Returns sections */
  public function sections(): \Traversable {
    foreach ($this->props as $p) {
      foreach ($p->sections() as $section) {
        yield $section;
      }
    }
  }
}
