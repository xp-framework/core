<?php namespace net\xp_framework\unittest\util;

use lang\IllegalArgumentException;
use unittest\{Expect, Test};
use util\Currency;

class CurrencyTest extends \unittest\TestCase {

  #[Test]
  public function get_instance_usd() {
    $this->assertEquals(Currency::$USD, Currency::getInstance('USD'));
  }

  #[Test]
  public function get_instance_eur() {
    $this->assertEquals(Currency::$EUR, Currency::getInstance('EUR'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function get_instance_nonexistant() {
    Currency::getInstance('@@not-a-currency@@');
  }
}