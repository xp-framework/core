<?php namespace util;

use lang\Value;

/**
 * Objects utility methods
 *
 * @test  xp://net.xp_framework.unittest.util.ObjectsTest
 */
abstract class Objects {

  /** Returns whether to objects are equal */
  public static function equal($a, $b): bool {
    if ($a instanceof Value) {
      return 0 === $a->compareTo($b);
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

  /** Compares two objects */
  public static function compare($a, $b): int {
    if ($a instanceof Value) {
      return $a->compareTo($b);
    } else if (is_array($a)) {
      if (!is_array($b)) return 1;
      if (0 !== $r= sizeof($a) <=> sizeof($b)) return $r;
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
      return $a <=> $b;
    }
  }

  /**
   * Returns a string representation
   *
   * @param  var $val
   * @param  string $indent
   * @return string
   */
  public static function stringOf($val, $indent= ''): string {
    static $protect= [];

    if (null === $val) {
      return 'null';
    } else if (is_string($val)) {
      return '"'.$val.'"';
    } else if (is_bool($val)) {
      return $val ? 'true' : 'false';
    } else if (is_int($val) || is_float($val)) {
      return (string)$val;
    } else if (($val instanceof Value) && !isset($protect[$hash= (string)$val->hashCode()])) {
      $protect[$hash]= true;
      $s= $val->toString();
      unset($protect[$hash]);
      return $indent ? str_replace("\n", "\n".$indent, $s) : $s;
    } else if (is_array($val)) {
      if (empty($val)) return '[]';
      $hash= print_r($val, true);
      if (isset($protect[$hash])) return '->{:recursion:}';
      $protect[$hash]= true;
      if (0 === $key= key($val)) {
        $r= '';
        foreach ($val as $value) {
          $r.= ', '.self::stringOf($value, $indent);
        }
        $r= '['.substr($r, 2).']';
      } else if (1 === sizeof($val)) {
        $r= '['.$key.' => '.self::stringOf($val[$key], $indent.'  ').']';
      } else {
        $r= "[\n";
        foreach ($val as $key => $val) {
          $r.= $indent.'  '.$key.' => '.self::stringOf($val, $indent.'  ')."\n";
        }
        $r.= $indent.']';
      }
      unset($protect[$hash]);
      return $r;
    } else if ($val instanceof \Closure) {
      $sig= '';
      $f= new \ReflectionFunction($val);
      foreach ($f->getParameters() as $p) {
        $sig.= ', $'.$p->name;
      }
      return '<function('.substr($sig, 2).')>';
    } else if (is_object($val)) {
      $hash= spl_object_hash($val);
      if (isset($protect[$hash])) return '->{:recursion:}';
      $protect[$hash]= true;
      $r= nameof($val)." {\n";
      foreach ((array)$val as $key => $value) {
        $r.= $indent.'  '.$key.' => '.self::stringOf($value, $indent.'  ')."\n";
      }
      unset($protect[$hash]);
      return $r.$indent.'}';
    } else if (is_resource($val)) {
      return 'resource(type= '.get_resource_type($val).', id= '.(int)$val.')';
    }
  }

  /** Returns a hash code */
  public static function hashOf($val): string {
    if (null === $val) {
      return 'N;';
    } else if ($val instanceof Value) {
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
