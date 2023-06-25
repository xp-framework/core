<?php namespace lang\unittest;

use lang\Generic;

#[Generic(self: 'T')]
class Unserializer {

  /**
   * Creates a new instance of a given value
   *
   * @param   var $arg
   * @return  T element
   */
  #[Generic(return: 'T')]
  public function newInstance($arg= null) {
    return null === $arg ? $T->default : $T->newInstance($arg);
  }
}