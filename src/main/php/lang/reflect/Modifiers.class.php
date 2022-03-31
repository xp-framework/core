<?php namespace lang\reflect;

/**
 * This class provides static methods to convert the numerical value
 * access modifiers (public, private, protected, final, abstract, 
 * static) are encoded in (as a bitfield) into strings.
 *
 * @see   xp://lang.reflect.Routine#getModifiers
 * @see   xp://lang.reflect.Field#getModifiers
 * @test  xp://net.xp_framework.unittest.reflection.ModifiersTest
 */
abstract class Modifiers {

  /**
   * Returns TRUE when the given modifiers include the public modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isPublic($m) {
    return 0 === $m || MODIFIER_PUBLIC === ($m & MODIFIER_PUBLIC);
  }

  /**
   * Returns TRUE when the given modifiers include the private modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isPrivate($m) {
    return MODIFIER_PRIVATE === ($m & MODIFIER_PRIVATE);
  }

  /**
   * Returns TRUE when the given modifiers include the protected modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isProtected($m) {
    return MODIFIER_PROTECTED === ($m & MODIFIER_PROTECTED);
  }

  /**
   * Returns TRUE when the given modifiers include the abstract modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isAbstract($m) {
    return MODIFIER_ABSTRACT === ($m & MODIFIER_ABSTRACT);
  }

  /**
   * Returns TRUE when the given modifiers include the final modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isFinal($m) {
    return MODIFIER_FINAL === ($m & MODIFIER_FINAL);
  }

  /**
   * Returns TRUE when the given modifiers include the static modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isStatic($m) {
    return MODIFIER_STATIC === ($m & MODIFIER_STATIC);
  }

  /**
   * Returns TRUE when the given modifiers include the readonly modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isReadonly($m) {
    return MODIFIER_READONLY === ($m & MODIFIER_READONLY);
  }

  /**
   * Returns TRUE when the given modifiers include the sealed modifier.
   *
   * @param  int $m
   * @return bool
   */
  public static function isSealed($m) {
    return MODIFIER_SEALED === ($m & MODIFIER_SEALED);
  }

  /**
   * Retrieves modifier names as an array. The order in which the 
   * modifiers are returned is the following:
   *
   * `[access] static abstract final readonly`
   *
   * [access] is one on public, private or protected.
   *
   * @param  int $m
   * @return string[]
   */
  public static function namesOf($m) {
    $names= [];
    switch ($m & (MODIFIER_PUBLIC | MODIFIER_PROTECTED | MODIFIER_PRIVATE)) {
      case MODIFIER_PRIVATE: $names[]= 'private'; break;
      case MODIFIER_PROTECTED: $names[]= 'protected'; break;
      case MODIFIER_PUBLIC: default: $names[]= 'public'; break;
    }
    if ($m & MODIFIER_SEALED) $names[]= 'sealed';
    if ($m & MODIFIER_STATIC) $names[]= 'static';
    if ($m & MODIFIER_ABSTRACT) $names[]= 'abstract';
    if ($m & MODIFIER_FINAL) $names[]= 'final';
    if ($m & MODIFIER_READONLY) $names[]= 'readonly';
    return $names;
  }

  /**
   * Retrieves modifier names as a string
   *
   * @param  int $m
   * @return string
   */
  public static function stringOf($m) {
    return implode(' ', self::namesOf($m));
  }
}
