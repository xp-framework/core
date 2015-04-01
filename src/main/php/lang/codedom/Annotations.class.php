<?php namespace lang\codedom;

use lang\Objects;

class Annotations extends \lang\Object {
  private $list;

  public function __construct($list) {
    $this->list= $list;
  }

  public function resolve($context, $imports= []) {
    $resolved= [];
    foreach ($this->list as $annotation) {
      $resolved[$annotation->target()][$annotation->name()]= $annotation->resolve($context, $imports);
    }
    return $resolved;
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && Objects::equal($this->list, $cmp->list);
  }
}