<?php namespace lang\types;

/**
 * The Short class wraps a value of the type short 
 * 
 * Range: -2^15 - (2^15)- 1
 *
 * @deprecated Wrapper types will move to their own library
 */
class Short extends Number {

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
    throw new \lang\IllegalArgumentException('Not a short: '.$value);
  }
}
