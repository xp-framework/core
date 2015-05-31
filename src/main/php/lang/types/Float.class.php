<?php namespace lang\types;

/**
 * The Float class wraps a value of the type float
 *
 * @deprecated Wrapper types will move to their own library
 */
class Float extends Number {

  /**
   * ValueOf factory
   *
   * @param   string $value
   * @return  self
   * @throws  lang.IllegalArgumentException
   */
  public static function valueOf($value) {
    if (!is_numeric($value)) {
      throw new \lang\IllegalArgumentException('Not a number: '.$value);
    }
    return new self($value);
  }
}
