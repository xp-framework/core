<?php namespace lang\types;

/**
 * The Double class wraps a value of the type double
 */
class Double extends Number {

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
