<?php namespace net\xp_framework\unittest\core\generics;

/**
 * Unserializer
 */
#[@generic(self= 'T')]
class Unserializer {

  /**
   * Creates a new instance of a given value
   *
   * @param   var $arg
   * @return  T element
   */
  #[@generic(return= 'T')]
  public function newInstance($arg= null) {
    return null === $arg ? $T->default : $T->newInstance($arg);
  }
}