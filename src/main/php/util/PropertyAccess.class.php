<?php namespace util;

/**
 * PropertyAccess interface
 */
interface PropertyAccess {

  /**
   * Read array value
   *
   * @param   string section
   * @param   string key
   * @param   var default default []
   * @return  string[]
   */
  public function readArray($section, $key, $default= []);

  /**
   * Read map value
   *
   * @param   string section
   * @param   string key
   * @param   var default
   * @return  [:var]
   */
  public function readMap($section, $key, $default= null);

  /**
   * Read bool value
   *
   * @param   string section
   * @param   string key
   * @param   bool default default FALSE
   * @return  bool
   */
  public function readBool($section, $key, $default= false);

  /**
   * Read string value
   *
   * @param   string section
   * @param   string key
   * @param   var default default NULL
   * @return  string
   */
  public function readString($section, $key, $default= null);

  /**
   * Read integer value
   *
   * @param   string section
   * @param   string key
   * @param   var default default 0
   * @return  int
   */
  public function readInteger($section, $key, $default= 0);

  /**
   * Read float value
   *
   * @param   string section
   * @param   string key
   * @param   var default default []
   * @return  float
   */
  public function readFloat($section, $key, $default= 0.0);

  /**
   * Read section
   *
   * @param   string section
   * @param   var default default []
   * @return  [:string]
   */
  public function readSection($section, $default= []);

  /**
   * Read range value
   *
   * @param   string section
   * @param   string key
   * @param   var default default 0.0
   * @return  int[]
   */
  public function readRange($section, $key, $default= []);

  /**
   * Test whether a given section exists
   *
   * @param   string section
   * @return  bool
   */
  public function hasSection($section);

}

