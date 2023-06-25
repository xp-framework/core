<?php namespace lang\unittest;

use test\{Assert, Test};

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
      create('new lang.unittest.Nullable<lang.Value>', $this->value)->get()
    );
  }

  #[Test]
  public function create_with_null() {
    Assert::false(create('new lang.unittest.Nullable<lang.Value>', null)->hasValue());
  }

  #[Test]
  public function set_value() {
    Assert::equals($this->value, create('new lang.unittest.Nullable<lang.Value>', $this->value)
      ->set($this->value)
      ->get()
    );
  }

  #[Test]
  public function set_null() {
    Assert::false(create('new lang.unittest.Nullable<lang.Value>', $this->value)
      ->set(null)
      ->hasValue()
    );
  }
}