<?php namespace lang\unittest;

class PrivateAccessibilityFixtureCtorChild extends PrivateAccessibilityFixture {

  /**
   * Entry point: Invoke constructor
   *
   * @param   lang.XPClass
   * @return  lang.unittest.PrivateAccessibilityFixture
   */
  public static function construct(\lang\XPClass $class) {
    return $class->getConstructor()->newInstance([]);
  }
}