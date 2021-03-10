<?php namespace net\xp_framework\unittest\core;

/**
 * Operation enumeration
 */
abstract class Operation extends \lang\Enum {
  public static
    $plus,
    $minus,
    $times,
    $divided_by;
  
  static function __static() {
    self::$plus= new class(0, 'plus') extends Operation {
      static function __static() { }
      public function evaluate($x, $y) { return $x + $y; } 
    };
    self::$minus= new class(1, 'minus') extends Operation {
      static function __static() { }
      public function evaluate($x, $y) { return $x - $y; } 
    };
    self::$times= new class(2, 'times') extends Operation {
      static function __static() { }
      public function evaluate($x, $y) { return $x * $y; } 
    };
    self::$divided_by= new class(3, 'divided_by') extends Operation {
      static function __static() { }
      public function evaluate($x, $y) { return $x / $y; } 
    };
  }

  /**
   * Evaluates this operation
   *
   * @param   int x
   * @param   int y
   * @return  float
   */
  public abstract function evaluate($x, $y);
}
