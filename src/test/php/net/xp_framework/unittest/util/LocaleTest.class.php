<?php namespace net\xp_framework\unittest\util;

use unittest\Test;
use util\Locale;

class LocaleTest extends \unittest\TestCase {

  #[Test]
  public function get_default_locale() {
    $this->assertInstanceOf(Locale::class, Locale::getDefault());
  }

  #[Test]
  public function constructor_with_one_arg() {
    $this->assertEquals('de_DE', (new Locale('de_DE'))->toString());
  }

  #[Test]
  public function constructor_with_two_args() {
    $this->assertEquals('de_DE', (new Locale('de', 'DE'))->toString());
  }

  #[Test]
  public function constructor_with_three_args() {
    $this->assertEquals('de_DE@utf-8', (new Locale('de', 'DE', 'utf-8'))->toString());
  }

  #[Test]
  public function de_DE_language() {
    $this->assertEquals('de', (new Locale('de_DE'))->getLanguage());
  }

  #[Test]
  public function de_DE_country() {
    $this->assertEquals('DE', (new Locale('de_DE'))->getCountry());
  }

  #[Test]
  public function de_DE_variant() {
    $this->assertEquals('', (new Locale('de_DE'))->getVariant());
  }

  #[Test]
  public function de_DE_with_variant() {
    $this->assertEquals('@utf-8', (new Locale('de_DE@utf-8'))->getVariant());
  }
}