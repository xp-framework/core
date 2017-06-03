<?php namespace lang;

/**
 * Represents runtime options
 *
 * @test  xp://net.xp_framework.unittest.core.RuntimeOptionsTest
 * @see   xp://lang.Runtime#startupOptions
 */
class RuntimeOptions implements Value {
  protected $backing= [];

  /**
   * Constructor
   *
   * @param   [:var] backing default []
   */
  public function __construct($backing= []) {
    $this->backing= $backing;
  }
  
  /**
   * Set switch (e.g. "-q")
   *
   * @param   string name switch name without leading dash
   * @return  lang.RuntimeOptions this object
   */
  public function withSwitch($name) {
    $this->backing["\1".$name]= true; 
    return $this;
  }

  /**
   * Get switch (e.g. "-q")
   *
   * @param   string name switch name without leading dash
   * @param   bool default default FALSE
   * @return  bool
   */
  public function getSwitch($name, $default= false) {
    $key= "\1".$name;
    return isset($this->backing[$key])
      ? $this->backing[$key]
      : $default
    ;
  }
  
  /**
   * Get setting (e.g. "memory_limit")
   *
   * @param   string name
   * @param   string[] default default NULL
   * @return  string[] values
   */
  public function getSetting($name, $default= null) {
    $key= 'd'.$name;
    return isset($this->backing[$key])
      ? $this->backing[$key]
      : $default
    ;
  }

  /**
   * Set setting (e.g. "memory_limit")
   *
   * @param   string setting
   * @param   var value either a number, a string or an array of either
   * @param   bool add default FALSE
   * @return  lang.RuntimeOptions this object
   */
  public function withSetting($setting, $value, $add= false) {
    $key= 'd'.$setting;
    if (null === $value) {
      unset($this->backing[$key]);
    } else if ($add && isset($this->backing[$key])) {
      $this->backing[$key]= array_merge($this->backing[$key], (array)$value); 
    } else {
      $this->backing[$key]= (array)$value; 
    }
    return $this;
  }
  
  /**
   * Sets classpath
   *
   * @param   var element either an array of paths or a single path
   * @return  lang.RuntimeOptions this object
   */
  public function withClassPath($element) {
    $this->backing["\0cp"]= [];
    foreach ((array)$element as $path) {
      $this->backing["\0cp"][]= rtrim($path, DIRECTORY_SEPARATOR);
    }
    return $this;
  }
  
  /**
   * Gets classpath
   *
   * @return  string[]
   */
  public function getClassPath() {
    return $this->backing["\0cp"] ?? [];
  }

  /**
   * Return an array suitable for passing to lang.Process' constructor
   *
   * @return  string[]
   */
  public function asArguments() {
    $s= defined('HHVM_VERSION') ? ['--php'] : [];
    foreach ($this->backing as $key => $value) {
      if ("\1" === $key{0}) {
        $s[]= '-'.substr($key, 1);
      } else if ("\0" !== $key{0}) {
        foreach ($value as $v) {
          $s[]= '-'.$key{0};
          $s[]= substr($key, 1).'='.$v;
        }
      }
    }
    return $s;
  }

  /**
   * Creates a hash code of these options
   *
   * @return  string
   */
  public function hashCode() {
    return 'O'.md5(serialize($this->asArguments()));
  }

  /**
   * Creates a string representation of these options
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'@'.implode(' ', $this->asArguments());
  }

  /**
   * Returns whether another object is equal to these options
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->backing <=> $value->backing : 1;
  }
}
