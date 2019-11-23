<?php namespace net\xp_framework\unittest\reflection;

/**
 * Interface with overloaded methods
 *
 * @see   xp://lang.reflect.Proxy
 */
interface OverloadedInterface {
  
  /**
   * Overloaded method.
   *
   */
  #[@overloaded(['signatures' => [
  #  ['string'],
  #  ['string', 'string']
  #]])]
  public function overloaded();
}
