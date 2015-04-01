<?php namespace lang\codedom;

class Values extends \lang\Object {
  private $backing;

  public function __construct($values) {
    $this->backing= $values;
  }

  public function matched() { return true; }

  public function backing() { return $this->backing; }
}