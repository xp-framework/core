<?php namespace net\xp_framework\unittest\core\generics;

use lang\Object;

/**
 * Filter
 */
#[@generic(self= 'T')]
abstract class NSFilter extends Object {

  /**
   * Returns whether this filter accepts a given argument
   *
   * @param   T $t
   * @return  bool
   */
  #[@generic(params= 'T')]
  public abstract function accept($t);
}