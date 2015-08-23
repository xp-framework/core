<?php namespace net\xp_framework\unittest\util;

use util\Currency;
use lang\IllegalArgumentException;

class CurrencyTest extends \unittest\TestCase {

  #[@test]
  public function get_instance_usd() {
    $this->assertEquals(Currency::$USD, Currency::getInstance('USD'));
  }

  #[@test]
  public function get_instance_eur() {
    $this->assertEquals(Currency::$EUR, Currency::getInstance('EUR'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function get_instance_nonexistant() {
    Currency::getInstance('@@not-a-currency@@');
  }
}
