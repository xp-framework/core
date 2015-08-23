<?php namespace util;

use lang\Generic;
use lang\Value;

/**
 * Objects utility methods
 *
 * @test  xp://net.xp_framework.unittest.util.ObjectsTest
 */
abstract class Objects extends \lang\Object {

  /**
   * Returns whether to objects are equal
   *
   * @param   var $a
   * @param   var $b
   * @return  bool
   */
  public static function equal($a, $b) {
    if ($a instanceof Value) {
      return 0 === $a->compareTo($b);
    } else if ($a instanceof Generic) {
      return $a->equals($b);
    } else if (is_array($a)) {
      if (!is_array($b) || sizeof($a) !== sizeof($b)) return false;
      foreach ($a as $key => $val) {
        if (!array_key_exists($key, $b) || !self::equal($val, $b[$key])) return false;
      }
      return true;
    } else {
      return $a === $b;
    }
  }

  /**
   * Compares two objects
   *
   * @param   var $a
   * @param   var $b
   * @return  int
   */
  public static function compare($a, $b) {
    if ($a instanceof Value) {
      return $a->compareTo($b);
    } else if ($a instanceof Generic) {
      return $a->equals($b) ? 0 : ($a < $b ? -1 : 1);
    } else {
      return $a === $b ? 0 : ($a < $b ? -1 : 1);
    }
  }

  /**
   * Returns a string representation
   *
   * @param  var $val
   * @param  string $default the value to use for NULL
   * @return string
   */
  public static function stringOf($val, $default= '') {
    if (null === $val) {
      return $default;
    } else {
      return \xp::stringOf($val);
    }
  }

  /**
   * Returns a hash code
   *
   * @param  var $val
   * @return string
   */
  public static function hashOf($val) {
    if (null === $val) {
      return 'N;';
    } else if ($val instanceof Generic || $val instanceof Value) {
      return $val->hashCode();
    } else if ($val instanceof \Closure) {
      return spl_object_hash($val);
    } else {
      return serialize($val);
    }
  }
}
