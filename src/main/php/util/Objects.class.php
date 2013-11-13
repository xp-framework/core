<?php namespace util;

/**
 * Objects utility methods
 *
 * @test  xp://net.xp_framework.unittest.util.ObjectsTest
 */
abstract class Objects extends \lang\Object {

  /**
   * Returns whether to objects are equal
   *
   * @param   var a
   * @param   var b
   * @return  bool
   */
  public static function equal($a, $b) {
    if ($a instanceof \lang\Generic) {
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
   * Returns a string representation
   *
   * @param  var val
   * @param  string default the value to use for NULL
   * @return string
   */
  public static function stringOf($val, $default= '') {
    return null === $val ?
      $default :
      ($val instanceof \lang\Generic ? $val->toString() : \xp::stringOf($val))
    ;
  }

  /**
   * Returns a hash code
   *
   * @param  var val
   * @return string
   */
  public static function hashOf($val) {
    return null === $val ?
      'N;' :
      ($val instanceof \lang\Generic ? $val->hashCode() : serialize($val))
    ;
  }
}
