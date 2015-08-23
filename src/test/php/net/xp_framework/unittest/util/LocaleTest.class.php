<?php namespace net\xp_framework\unittest\util;

use util\Locale;

class LocaleTest extends \unittest\TestCase {

  #[@test]
  public function get_default_locale() {
    $this->assertInstanceOf(Locale::class, Locale::getDefault());
  }

  #[@test]
  public function constructor_with_one_arg() {
    $this->assertEquals('de_DE', (new Locale('de_DE'))->toString());
  }

  #[@test]
  public function constructor_with_two_args() {
    $this->assertEquals('de_DE', (new Locale('de', 'DE'))->toString());
  }

  #[@test]
  public function constructor_with_three_args() {
    $this->assertEquals('de_DE@utf-8', (new Locale('de', 'DE', 'utf-8'))->toString());
  }

  #[@test]
  public function de_DE_language() {
    $this->assertEquals('de', (new Locale('de_DE'))->getLanguage());
  }

  #[@test]
  public function de_DE_country() {
    $this->assertEquals('DE', (new Locale('de_DE'))->getCountry());
  }

  #[@test]
  public function de_DE_variant() {
    $this->assertEquals('', (new Locale('de_DE'))->getVariant());
  }

  #[@test]
  public function de_DE_with_variant() {
    $this->assertEquals('@utf-8', (new Locale('de_DE@utf-8'))->getVariant());
  }
}
