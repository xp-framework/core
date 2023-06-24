<?php namespace net\xp_framework\unittest\core\generics;

use net\xp_framework\unittest\Name;
use unittest\{Assert, Test};

class OptionalArgTest {
  private $value;

  #[Before]
  public function value() {
    $this->value= new Name(self::class);
  }


  #[Test]
  public function create_with_value() {
    Assert::equals(
      $this->value,
      create('new net.xp_framework.unittest.core.generics.Nullable<lang.Value>', $this->value)->get()
    );
  }

  #[Test]
  public function create_with_null() {
    Assert::false(create('new net.xp_framework.unittest.core.generics.Nullable<lang.Value>', null)->hasValue());
  }

  #[Test]
  public function set_value() {
    Assert::equals($this->value, create('new net.xp_framework.unittest.core.generics.Nullable<lang.Value>', $this->value)
      ->set($this->value)
      ->get()
    );
  }

  #[Test]
  public function set_null() {
    Assert::false(create('new net.xp_framework.unittest.core.generics.Nullable<lang.Value>', $this->value)
      ->set(null)
      ->hasValue()
    );
  }
}