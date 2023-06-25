<?php namespace lang\unittest;

class FunctionTypeInvokeable {

  /**
   * Invocation handler
   *
   * @param  var $arg
   * @return var
   */
  public function __invoke($arg) { return $arg; }
}