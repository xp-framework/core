<?php namespace net\xp_framework\unittest\reflection;

/**
 * Fixture class for accessibility tests
 *
 * @see      xp://net.xp_framework.unittest.reflection.ProtectedAccessibilityTest
 */
class ProtectedAccessibilityFixture extends \lang\Object {
  protected $target= 'Target';
  protected static $staticTarget= 'Target';

  /**
   * Constructor
   *
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
  public static function construct(\lang\XPClass $class) {
    return $class->getConstructor()->newInstance([]);
  }

  /**
   * Entry point: Invoke target method
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function invoke(\lang\XPClass $class) {
    return $class->getMethod('target')->invoke(new self());
  }

  /**
   * Entry point: Invoke staticTarget method
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function invokeStatic(\lang\XPClass $class) {
    return $class->getMethod('staticTarget')->invoke(null);
  }

  /**
   * Entry point: Read target member
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function read(\lang\XPClass $class) {
    return $class->getField('target')->get(new self());
  }

  /**
   * Entry point: Read staticTarget member
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function readStatic(\lang\XPClass $class) {
    return $class->getField('staticTarget')->get(null);
  }

  /**
   * Entry point: Write target member, then read it back
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function write(\lang\XPClass $class) {
    with ($s= new self(), $f= $class->getField('target')); {
      $f->set($s, 'Modified');
      return $f->get($s);
    }
  }

  /**
   * Entry point: Write target member, then read it back
   *
   * @param   lang.XPClass
   * @return  string
   */
  public static function writeStatic(\lang\XPClass $class) {
    with ($f= $class->getField('staticTarget')); {
      $f->set(null, 'Modified');
      return $f->get(null);
    }
  }
}
