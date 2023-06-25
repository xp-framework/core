<?php namespace lang\unittest;

use util\Objects;

trait CompareTo {

  /** @type int */
  private static $NOT_INSTANCE = 1;

  /**
   * Compares a given value to this
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this, $value) : self::$NOT_INSTANCE;
  }
}