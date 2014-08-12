<?php namespace net\xp_framework\unittest\core\generics;

use lang\Type;

/**
 * List of elements
 */
#[@generic(self= 'T')]
class Unserializer extends \lang\Object {

  /**
   * Returns a list of all elements
   *
   * @param   var $arg
   * @return  T element
   */
  #[@generic(return= 'T')]
  public function newInstance($arg= null) {
    $type= Type::forName($T);
    return null === $arg ? $type->default : $type->newInstance($arg);
  }
}