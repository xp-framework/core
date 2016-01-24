<?php namespace util;

use lang\Generic;
use lang\Value;

/**
 * Objects utility methods
 *
 * @test  xp://net.xp_framework.unittest.util.ObjectsTest
 */
abstract class Objects {

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
      return $a === $b || (
        is_object($a) && is_object($b) &&
        get_class($a) === get_class($b) &&
        self::equal((array)$a, (array)$b)
      );
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
    } else if (is_array($a)) {
      if (!is_array($b)) return 1;
      if (sizeof($a) < sizeof($b)) return -1;
      if (sizeof($a) > sizeof($b)) return 1;
      foreach ($a as $key => $val) {
        if (!array_key_exists($key, $b)) return 1;
        if (0 !== $r= self::compare($val, $b[$key])) return $r;
      }
      return 0;
    } else if ($a === $b) {
      return 0;
    } else if (is_object($a)) {
      return (is_object($b) && get_class($a) === get_class($b))
        ? self::compare((array)$a, (array)$b)
        : 1
      ;
    } else {
      return $a < $b ? -1 : 1;
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
    } else if (is_array($val)) {
      $s= '';
      foreach ($val as $key => $value) {
        $s.= '|'.$key.':'.self::hashOf($value);
      }
      return $s;
    } else {
      return is_object($val) ? spl_object_hash($val) : serialize($val);
    }
  }
}
