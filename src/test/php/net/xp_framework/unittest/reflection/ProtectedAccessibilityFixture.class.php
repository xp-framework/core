<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;

/**
 * Fixture class for accessibility tests
 *
 * @see   xp://net.xp_framework.unittest.reflection.ProtectedAccessibilityTest
 */
class ProtectedAccessibilityFixture {
  protected $target= 'Target';
  protected static $staticTarget= 'Target';

  /**
   * Constructor
   */
  protected function __construct() { }

  /**
   * Target method
   *
   * @return  string
   */
  protected function target() { 
    return 'Invoked';
  }
 
  /**
   * Target method
   *
   * @return  string
   */
  protected static function staticTarget() { 
    return 'Invoked';
  }
 
  /**
   * Entry point: Invoke constructor
   *
   * @param   lang.XPClass
   * @return  net.xp_framework.unittest.reflection.ProtectedAccessibilityFixture
   */
  public static function construct(XPClass $class) {
    return $class->getConstructor()->newInstance([]);
  }

  /**
   * Entry point: Invoke target method
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function invoke(XPClass $class) {
    return $class->getMethod('target')->invoke(new self());
  }

  /**
   * Entry point: Invoke staticTarget method
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function invokeStatic(XPClass $class) {
    return $class->getMethod('staticTarget')->invoke(null);
  }

  /**
   * Entry point: Read target member
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function read(XPClass $class) {
    return $class->getField('target')->get(new self());
  }

  /**
   * Entry point: Read staticTarget member
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function readStatic(XPClass $class) {
    return $class->getField('staticTarget')->get(null);
  }

  /**
   * Entry point: Write target member, then read it back
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function write(XPClass $class) {
    return with (new self(), $class->getField('target'), function($self, $f) {
      $f->set($self, 'Modified');
      return $f->get($self);
    });
  }

  /**
   * Entry point: Write target member, then read it back
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function writeStatic(XPClass $class) {
    return with ($class->getField('staticTarget'), function($f) {
      $f->set(null, 'Modified');
      return $f->get(null);
    });
  }
}