<?php namespace util\unittest;

use lang\IllegalArgumentException;
use test\verify\Runtime;
use test\{Action, Assert, Expect, Test};
use util\{Currency, Money};

#[Runtime(extensions: ['bcmath'])]
class MoneyTest {

  #[Test]
  public function tenUsDollarsFromInt() {
    Assert::equals(
      '10.00', 
      (new Money(10, Currency::$USD))->amount(2)
    );
  }

  #[Test]
  public function tenUsDollarsFromFloat() {
    Assert::equals(
      '10.00', 
      (new Money(10.00, Currency::$USD))->amount(2)
    );
  }

  #[Test]
  public function tenUsDollarsFromString() {
    Assert::equals(
      '10.00', 
      (new Money('10.00', Currency::$USD))->amount(2)
    );
  }

  #[Test]
  public function currency() {
    Assert::equals(Currency::$USD, (new Money('1.00', Currency::$USD))->currency());
  }

  #[Test]
  public function stringRepresentation() {
    Assert::equals(
      '19.99 USD', 
      (new Money('19.99', Currency::$USD))->toString()
    );
  }

  #[Test]
  public function add() {
    Assert::equals(
      new Money('20.00', Currency::$EUR),
      (new Money('11.50', Currency::$EUR))->add(new Money('8.50', Currency::$EUR))
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannotAddDifferentCurrencies() {
    (new Money('11.50', Currency::$EUR))->add(new Money('8.50', Currency::$USD));
  }

  #[Test]
  public function subtract() {
    Assert::equals(
      new Money('3.00', Currency::$EUR),
      (new Money('11.50', Currency::$EUR))->subtract(new Money('8.50', Currency::$EUR))
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannotSubtractDifferentCurrencies() {
    (new Money('11.50', Currency::$EUR))->subtract(new Money('8.50', Currency::$USD));
  }

  #[Test]
  public function multiplyBy() {
    Assert::equals(
      new Money('2.98', Currency::$EUR),
      (new Money('1.49', Currency::$EUR))->multiplyBy(2)
    );
  }

  #[Test]
  public function divideBy() {
    Assert::equals(
      new Money('9.99', Currency::$EUR),
      (new Money('19.98', Currency::$EUR))->divideBy(2)
    );
  }

  #[Test]
  public function compareToReturnsZeroOnEquality() {
    Assert::equals(
      0,
      (new Money('1.01', Currency::$EUR))->compareTo(new Money('1.01', Currency::$EUR))
    );
  }

  #[Test]
  public function compareToReturnsNegativeOneIfArgumentIsLess() {
    Assert::equals(
      -1,
      (new Money('1.01', Currency::$EUR))->compareTo(new Money('0.99', Currency::$EUR))
    );
  }

  #[Test]
  public function compareToReturnsOneIfArgumentIsMore() {
    Assert::equals(
      1,
      (new Money('0.99', Currency::$EUR))->compareTo(new Money('1.01', Currency::$EUR))
    );
  }

  #[Test]
  public function comparingDifferentCurrencies() {
    Assert::equals(
      1,
      (new Money('1.01', Currency::$EUR))->compareTo(new Money('0.99', Currency::$USD))
    );
  }
  
  #[Test]
  public function tenGallonsOfRegular() {
    Assert::equals(
      new Money('32.99', Currency::$EUR),
      (new Money('3.299', Currency::$EUR))->multiplyBy(10)
    );
  }

  #[Test]
  public function aThousandEurosInDollars() {
    Assert::equals(
      new Money('1496.64', Currency::$EUR),
      (new Money('1000.00', Currency::$EUR))->multiplyBy(1.49664)
    );
  }
}