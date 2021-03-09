<?php namespace net\xp_framework\unittest\core;

use lang\XPEnum;

/**
 * Weekday enumeration
 */
class Weekday extends XPEnum {
  public static $MON= 1, $TUE, $WED, $THU, $FRI, $SAT, $SUN;

  // These properties verify the automatic initialization doesn't choke on
  // non-enum members (which must be public static) @ see Enum::__static()
  private static $of;
  private $weekend;
}
