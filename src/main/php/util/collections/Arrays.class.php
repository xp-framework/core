<?php namespace util\collections;

use lang\types\ArrayList;
use util\Comparator;
use lang\Generic;
use lang\Value;

/**
 * Various methods for working with arrays.
 *
 * @test  xp://net.xp_framework.unittest.util.collections.ArraysTest
 * @see   xp://util.collections.IList
 * @see   xp://lang.types.ArrayList
 */
abstract class Arrays extends \lang\Object {
  public static $EMPTY= null;

  static function __static() {
    self::$EMPTY= new ArrayList();
  }
  
  /**
   * Returns an IList instance for a given array
   *
   * @param   lang.types.ArrayList array
   * @return  util.collections.IList
   */
  public static function asList(ArrayList $array) {
    $v= new Vector();
    $v->addAll($array->values);
    return $v;
  }
  
  /**
   * Sorts an array
   *
   * @see     php://sort
   * @param   lang.types.ArrayList array
   * @param   var method
   */
  public static function sort(ArrayList $array, $method= SORT_REGULAR) {
    if ($method instanceof Comparator) {
      usort($array->values, [$method, 'compare']);
    } else {
      sort($array->values, $method);
    }
  }

  /**
   * Returns a sorted array
   *
   * @see     php://sort
   * @param   lang.types.ArrayList array
   * @param   var method
   * @return  lang.types.ArrayList the sorted array
   */
  public static function sorted(ArrayList $array, $method= SORT_REGULAR) {
    $copy= clone $array;
    if ($method instanceof Comparator) {
      usort($copy->values, [$method, 'compare']);
    } else {
      sort($copy->values, $method);
    }
    return $copy;
  }

  /**
   * Checks whether a certain value is contained in an array
   *
   * @param   lang.types.ArrayList array
   * @param   var search
   * @return  bool
   */
  public static function contains(ArrayList $array, $search) {
    if ($search instanceof Generic) {
      foreach ($array->values as $value) {
        if ($search->equals($value)) return true;
      }
    } else if ($search instanceof Value) {
      foreach ($array->values as $value) {
        if (0 === $search->compareTo($value)) return true;
      }
    } else {
      foreach ($array->values as $value) {
        if ($search === $value) return true;
      }
    }
    return false;
  }
}
