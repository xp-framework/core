<?php namespace net\xp_framework\unittest\core;

trait Named {
  private $name;

  /** @return string */
  public function name() { return $this->name; }
}
