<?php namespace util\unittest;

use unittest\{Assert, Test};
use util\Locale;

class LocaleTest {

  #[Test]
  public function get_default_locale() {
    Assert::instance(Locale::class, Locale::getDefault());
  }

  #[Test]
  public function constructor_with_one_arg() {
    Assert::equals('de_DE', (new Locale('de_DE'))->toString());
  }

  #[Test]
  public function constructor_with_two_args() {
    Assert::equals('de_DE', (new Locale('de', 'DE'))->toString());
  }

  #[Test]
  public function constructor_with_three_args() {
    Assert::equals('de_DE@utf-8', (new Locale('de', 'DE', 'utf-8'))->toString());
  }

  #[Test]
  public function de_DE_language() {
    Assert::equals('de', (new Locale('de_DE'))->getLanguage());
  }

  #[Test]
  public function de_DE_country() {
    Assert::equals('DE', (new Locale('de_DE'))->getCountry());
  }

  #[Test]
  public function de_DE_variant() {
    Assert::equals('', (new Locale('de_DE'))->getVariant());
  }

  #[Test]
  public function de_DE_with_variant() {
    Assert::equals('@utf-8', (new Locale('de_DE@utf-8'))->getVariant());
  }
}