<?php namespace lang\codedom;

class Parameter extends \lang\Object {
  private $name, $restriction, $type, $default;

  /**
   * Creates a new method declaration
   *
   * @param  string $name
   * @param  string $restriction
   * @param  string $default
   * @param  string $type
   */
  public function __construct($name, $restriction, $default= null, $type= null) {
    $this->name= $name;
    $this->restriction= $restriction;
    $this->type= $type;
    $this->default= $default;
  }

  /** @return string */
  public function name() { return $this->name; }

  /** @return string */
  public function restriction() { return $this->restriction; }

  /** @return bool */
  public function hasDefault() { return null !== $this->default; }

  /** @return string */
  public function defaultValue() { return $this->default; }

  /** @return string */
  public function type() { return $this->type; }

  /** @param string $type */
  public function typed($type) { $this->type= $type; }

  /** @param string $default */
  public function orElse($default) { $this->default= $default; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      '%s%s%s',
      $this->restriction ? $this->restriction.' ' : '',
      $this->name,
      $this->default ? '= '.$this->default : ''
    );
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->name === $cmp->name &&
      $this->restriction === $cmp->restriction &&
      $this->default === $cmp->default
    );
  }
}