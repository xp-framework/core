<?php namespace net\xp_framework\unittest\util;

use lang\IllegalArgumentException;
use unittest\{Assert, Expect, Test};
use util\Currency;

class CurrencyTest {

  #[Test]
  public function get_instance_usd() {
    Assert::equals(Currency::$USD, Currency::getInstance('USD'));
  }

  #[Test]
  public function get_instance_eur() {
    Assert::equals(Currency::$EUR, Currency::getInstance('EUR'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function get_instance_nonexistant() {
    Currency::getInstance('@@not-a-currency@@');
  }
}