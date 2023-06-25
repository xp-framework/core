<?php namespace lang\unittest;

trait Named {
  private $name;

  /** @return string */
  public function name() { return $this->name; }
}