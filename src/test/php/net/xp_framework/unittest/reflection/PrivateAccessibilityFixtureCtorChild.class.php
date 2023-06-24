<?php namespace net\xp_framework\unittest\reflection;

class PrivateAccessibilityFixtureCtorChild extends PrivateAccessibilityFixture {

  /**
   * Entry point: Invoke constructor
   *
   * @param   lang.XPClass
   * @return  net.xp_framework.unittest.reflection.PrivateAccessibilityFixture
   */
  public static function construct(\lang\XPClass $class) {
    return $class->getConstructor()->newInstance([]);
  }
}