<?php namespace net\xp_framework\unittest\util;

use util\Money;
use util\Currency;
use lang\IllegalArgumentException;

/**
 * TestCase
 *
 * @see      xp://util.Money
 */
class MoneyTest extends \unittest\TestCase {

  #[@test]
  public function tenUsDollarsFromInt() {
    $this->assertEquals(
      '10.00', 
      (new Money(10, Currency::$USD))->amount(2)
    );
  }

  #[@test]
  public function tenUsDollarsFromFloat() {
    $this->assertEquals(
      '10.00', 
      (new Money(10.00, Currency::$USD))->amount(2)
    );
  }

  #[@test]
  public function tenUsDollarsFromString() {
    $this->assertEquals(
      '10.00', 
      (new Money('10.00', Currency::$USD))->amount(2)
    );
  }

  #[@test]
  public function currency() {
    $this->assertEquals(Currency::$USD, (new Money('1.00', Currency::$USD))->currency());
  }

  #[@test]
  public function stringRepresentation() {
    $this->assertEquals(
      '19.99 USD', 
      (new Money('19.99', Currency::$USD))->toString()
    );
  }

  #[@test]
  public function add() {
    $this->assertEquals(
      new Money('20.00', Currency::$EUR),
      (new Money('11.50', Currency::$EUR))->add(new Money('8.50', Currency::$EUR))
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannotAddDifferentCurrencies() {
    (new Money('11.50', Currency::$EUR))->add(new Money('8.50', Currency::$USD));
  }

  #[@test]
  public function subtract() {
    $this->assertEquals(
      new Money('3.00', Currency::$EUR),
      (new Money('11.50', Currency::$EUR))->subtract(new Money('8.50', Currency::$EUR))
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannotSubtractDifferentCurrencies() {
    (new Money('11.50', Currency::$EUR))->subtract(new Money('8.50', Currency::$USD));
  }

  #[@test]
  public function multiplyBy() {
    $this->assertEquals(
      new Money('2.98', Currency::$EUR),
      (new Money('1.49', Currency::$EUR))->multiplyBy(2)
    );
  }

  #[@test]
  public function divideBy() {
    $this->assertEquals(
      new Money('9.99', Currency::$EUR),
      (new Money('19.98', Currency::$EUR))->divideBy(2)
    );
  }

  #[@test]
  public function compareToReturnsZeroOnEquality() {
    $this->assertEquals(
      0,
      (new Money('1.01', Currency::$EUR))->compareTo(new Money('1.01', Currency::$EUR))
    );
  }

  #[@test]
  public function compareToReturnsNegativeOneIfArgumentIsLess() {
    $this->assertEquals(
      -1,
      (new Money('1.01', Currency::$EUR))->compareTo(new Money('0.99', Currency::$EUR))
    );
  }

  #[@test]
  public function compareToReturnsOneIfArgumentIsMore() {
    $this->assertEquals(
      1,
      (new Money('0.99', Currency::$EUR))->compareTo(new Money('1.01', Currency::$EUR))
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannotCompareDifferentCurrencies() {
    (new Money('1.01', Currency::$EUR))->compareTo(new Money('0.99', Currency::$USD));
  }
  
  #[@test]
  public function tenGallonsOfRegular() {
    $this->assertEquals(
      new Money('32.99', Currency::$EUR),
      (new Money('3.299', Currency::$EUR))->multiplyBy(10)
    );
  }

  #[@test]
  public function aThousandEurosInDollars() {
    $this->assertEquals(
      new Money('1496.64', Currency::$EUR),
      (new Money('1000.00', Currency::$EUR))->multiplyBy(1.49664)
    );
  }
}
