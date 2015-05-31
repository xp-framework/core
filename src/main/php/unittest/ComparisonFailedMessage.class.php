<?php namespace unittest;

use lang\Generic;
use lang\Value;

/**
 * The message for an assertion failure
 *
 * @see  xp://unittest.AssertionFailedError
 * @test xp://net.xp_framework.unittest.tests.AssertionMessagesTest
 */
class ComparisonFailedMessage extends \lang\Object implements AssertionFailedMessage {
  const CONTEXT_LENGTH = 20;

  protected $comparison;
  protected $expect;
  protected $actual;

  /**
   * Constructor
   *
   * @param   string message
   * @param   var expect
   * @param   var actual
   */
  public function __construct($comparison, $expect, $actual) {
    $this->comparison= $comparison;
    $this->expect= $expect;
    $this->actual= $actual;
  }

  /**
   * Creates a string representation of a given value.
   *
   * @param   var value
   * @param   string type NULL if type name should be not included.
   * @return  string
   */
  protected function stringOf($value, $type) {
    return (null === $value || null === $type ? '' : $type.':').\xp::stringOf($value);
  }

  /**
   * Compacts a string
   *
   * @param  string s
   * @param  int p common postfix offset
   * @param  int s common suffix offset
   * @param  int l length of the given string
   */
  protected function compact($str, $p, $s, $l) {
    $result= substr($str, $p, $s- $p);
    if ($p > 0) {
      $result= ($p < self::CONTEXT_LENGTH ? substr($str, 0, $p) : '...').$result; 
    }
    if ($s < $l) {
      $result= $result.($l- $s < self::CONTEXT_LENGTH ? substr($str, $s) : '...');
    }
    return '"'.$result.'"';
  }

  /**
   * Return formatted message - "expected ... but was .... using: ..."
   *
   * @return  string
   */
  public function format() {
    if (is_string($this->expect) && is_string($this->actual)) {
      $la= strlen($this->actual);
      $le= strlen($this->expect);
      for ($i= 0, $l= min($le, $la); $i < $l; $i++) {                     // Search from beginning
        if ($this->expect{$i} !== $this->actual{$i}) break;
      }
      for ($j= $le- 1, $k= $la- 1; $k >= $i && $j >= $i; $k--, $j--) {    // Search from end
        if ($this->expect{$j} !== $this->actual{$k}) break;
      }
      $expect= $this->compact($this->expect, $i, $j+ 1, $le);
      $actual= $this->compact($this->actual, $i, $k+ 1, $la);
    } else if ($this->expect instanceof Generic && $this->actual instanceof Generic) {
      $expect= $this->stringOf($this->expect, null);
      $actual= $this->stringOf($this->actual, null);
    } else if ($this->expect instanceof Value && $this->actual instanceof Value) {
      $expect= $this->expect->toString();
      $actual= $this->actual->toString();
    } else {
      $te= \xp::typeOf($this->expect);
      $ta= \xp::typeOf($this->actual);
      $include= $te !== $ta;
      $expect= $this->stringOf($this->expect, $include ? $te : null);
      $actual= $this->stringOf($this->actual, $include ? $ta : null);
    }

    return sprintf(
      "expected [%s] but was [%s] using: '%s'",
      $expect,
      $actual,
      $this->comparison
    );
  }
}