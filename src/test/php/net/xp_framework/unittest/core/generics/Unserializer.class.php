<?php namespace net\xp_framework\unittest\core\generics;

use lang\Generic;

/**
 * Unserializer
 */
#[@generic(['self' => 'T'])]
class Unserializer {

  /**
   * Creates a new instance of a given value
   *
   * @param   var $arg
   * @return  T element
   */
  #[Generic(['return' => 'T'])]
  public function newInstance($arg= null) {
    return null === $arg ? $T->default : $T->newInstance($arg);
  }
}