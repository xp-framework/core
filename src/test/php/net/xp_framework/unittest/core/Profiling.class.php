<?php namespace net\xp_framework\unittest\core;

use lang\Enum;

class Profiling extends Enum {
  public static $INSTANCE;
  public static $EXTENSION;

  public static $fixture= null;
  
  static function __static() {
    self::$INSTANCE= new self(0, 'INSTANCE');
    self::$EXTENSION= new self(1, 'EXTENSION');
  }
}