<?php namespace lang\types;

/**
 * The Long class wraps a value of the type long 
 * 
 * Range: -2^63 - (2^63)- 1
 *
 * @deprecated Wrapper types will move to their own library
 */
class Long extends Number {

  /**
   * ValueOf factory
   *
   * @param   string $value
   * @return  self
   * @throws  lang.IllegalArgumentException
   */
  public static function valueOf($value) {
    if (is_int($value) || is_string($value) && strspn($value, '0123456789') === strlen($value)) {
      return new self($value);
    }
    throw new \lang\IllegalArgumentException('Not a long: '.$value);
  }
}
