<?php namespace lang\codedom;

class Unexpected extends \lang\Object {
  
  public function __construct($message, $line) {
    $this->message= $message;
    $this->line= $line;
  }

  public function matched() { return false; }

  public function error() { return $this->message; }

  public function toString() { return $this->getClassName().'["'.$this->message.'" at line '.$this->line.']'; }
}