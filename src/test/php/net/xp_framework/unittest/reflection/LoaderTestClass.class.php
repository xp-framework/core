<?php namespace net\xp_framework\unittest\reflection;

class LoaderTestClass {
  private static $initializerCalled= false;

  static function __static() {
    self::$initializerCalled= true;
  }
  
  /** Returns whether the static initializer was called */
  public static function initializerCalled(): bool {
    return self::$initializerCalled;
  }
}