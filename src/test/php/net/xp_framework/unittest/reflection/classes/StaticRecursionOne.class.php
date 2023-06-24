<?php namespace net\xp_framework\unittest\reflection\classes;

use lang\XPClass;

class StaticRecursionOne {
  public static $two= null;

  static function __static() {
  
    // Load a class here
    self::$two= XPClass::forName('net.xp_framework.unittest.reflection.classes.StaticRecursionTwo');
  }
}