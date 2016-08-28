<?php namespace lang;

/**
 * Represents wildcards in a wildcard type. Package class, not to be 
 * publicly used.
 *
 * @see   xp://lang.WildcardType
 * @test  xp://net.xp_framework.unittest.core.WildcardTypeTest
 */
class Wildcard extends Type {
  public static $ANY;

  static function __static() {
    self::$ANY= new self('?', null);
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool { return true; }
}