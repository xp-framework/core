<?php namespace net\xp_framework\unittest\core;

/**
 * Fixture for generic method invocation
 */
class GenericMethodFixture extends \lang\Object {

  /**
   * Generic getter
   *
   * @param  var $arg
   * @return T
   */
  #[@generic(self= 'T', return= 'T')]
  public function get($T, $arg= null) {
    return null === $arg ? $T->default : $T->cast($arg);
  }

  /**
   * Generic creator
   *
   * @param  var $arg
   * @return T
   */
  #[@generic(self= 'T', return= 'T')]
  public static function newInstance($T, $arg= null) {
    return null === $arg ? $T->default : $T->newInstance($arg);
  }
}
