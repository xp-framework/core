<?php namespace lang\unittest;

abstract class BinaryOp {
  private $operator;

  public function __construct($operator) { $this->operator= $operator; }
  
  protected abstract function perform($a, $b);
}