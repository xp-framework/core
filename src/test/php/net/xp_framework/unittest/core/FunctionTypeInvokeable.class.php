<?php namespace net\xp_framework\unittest\core;

class FunctionTypeInvokeable {

  /**
   * Invocation handler
   *
   * @param  var $arg
   * @return var
   */
  public function __invoke($arg) { return $arg; }
}