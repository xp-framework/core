<?php namespace net\xp_framework\unittest\core;

abstract class BinaryOp {
  private $operator;

  public function __construct($operator) { $this->operator= $operator; }
  
  protected abstract function perform($a, $b);
}