<?php namespace lang;

class TypeParameter extends Type {

  static function __static() { }

  /** @param string $name */ 
  public function __construct($name) {
    parent::__construct($name, null);
  }
}