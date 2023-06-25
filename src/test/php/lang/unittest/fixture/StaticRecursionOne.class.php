<?php namespace lang\unittest\fixture;

use lang\XPClass;

class StaticRecursionOne {
  public static $two= null;

  static function __static() {
  
    // Load a class here
    self::$two= XPClass::forName('lang.unittest.fixture.StaticRecursionTwo');
  }
}