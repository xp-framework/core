<?php namespace lang\codedom;

class Unmatched extends \lang\Object {
  private $rule, $cause;
  
  public function __construct($rule, $cause) {
    $this->rule= $rule;
    $this->cause= $cause;
  }

  public function matched() { return false; }

  public function error() { return 'Umatched rule '.$this->rule->toString()."]\n  Caused by ".$this->cause->toString(); }

  public function toString() { return $this->getClassName().'@'.$this->error(); }
}