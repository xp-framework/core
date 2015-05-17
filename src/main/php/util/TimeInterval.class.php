<?php namespace util;

/**
 * Interval enumeration
 *
 * @test     xp://net.xp_framework.unittest.util.DateMathTest
 * @see      xp://util.DateMath
 */
class TimeInterval extends \lang\Enum {
  public static
    $YEAR,
    $MONTH,
    $DAY,
    $HOURS,
    $MINUTES,
    $SECONDS;

  static function __static() {
    self::$YEAR=    new self(0, 'year');
    self::$MONTH=   new self(1, 'month');
    self::$DAY=     new self(2, 'day');
    self::$HOURS=   new self(3, 'hours');
    self::$MINUTES= new self(4, 'minutes');
    self::$SECONDS= new self(5, 'seconds');
  }
}
