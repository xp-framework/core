<?php namespace net\xp_framework\unittest\core;

/** Class for FunctionTypeTest */
class FunctionTypeInvokeable {

  /**
   * @param  var $arg
   * @return var
   */
  public function __invoke($arg) { return $arg; }
}