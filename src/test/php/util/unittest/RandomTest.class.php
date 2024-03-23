<?php namespace util\unittest;

use lang\IllegalArgumentException;
use test\verify\{Condition, Runtime};
use test\{Action, Assert, Expect, Test, Values};
use util\Random;

class RandomTest {

  #[Test]
  public function can_create() {
    new Random();
  }

  #[Test]
  public function can_create_with_source() {
    new Random(Random::FAST);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unknown_source() {
    new Random('unknown');
  }

  #[Test]
  public function best_is_default_source() {
    Assert::equals(Random::BEST, (new Random())->source());
  }

  #[Test]
  public function passing_more_than_one_source_selects_first_available() {
    Assert::equals(Random::FAST, (new Random(['unknown', Random::FAST]))->source());
  }

  #[Test]
  public function bytes() {
    Assert::equals(20, (new Random())->bytes(20)->size());
  }

  #[Test]
  public function best_bytes() {
    Assert::equals(20, (new Random(Random::BEST))->bytes(20)->size());
  }

  #[Test]
  public function fast_bytes() {
    Assert::equals(20, (new Random(Random::FAST))->bytes(20)->size());
  }

  #[Test]
  public function secure_bytes() {
    Assert::equals(20, (new Random(Random::SECURE))->bytes(20)->size());
  }

  #[Test, Runtime(extensions: ['openssl'])]
  public function openssl_bytes() {
    Assert::equals(20, (new Random(Random::OPENSSL))->bytes(20)->size());
  }

  #[Test, Condition(assert: 'is_readable("/dev/urandom")')]
  public function urandom_bytes() {
    Assert::equals(20, (new Random(Random::URANDOM))->bytes(20)->size());
  }

  #[Test, Expect(IllegalArgumentException::class), Values([-1, 0])]
  public function cannot_use_limit_smaller_than_one($limit) {
    (new Random())->bytes($limit);
  }

  #[Test]
  public function int() {
    $random= (new Random())->int(0, 10);
    Assert::true($random >= 0 && $random <= 10);
  }

  #[Test]
  public function negative_int() {
    $random= (new Random())->int(-10, -1);
    Assert::true($random >= -10 && $random <= -1);
  }

  #[Test]
  public function limits_default_to_zero_to_int_max() {
    $random= (new Random())->int();
    Assert::true($random >= 0 && $random <= PHP_INT_MAX);
  }

  #[Test, Runtime(extensions: ['openssl'])]
  public function openssl_int() {
    $random= (new Random(Random::OPENSSL))->int(0, 10);
    Assert::true($random >= 0 && $random <= 10);
  }

  #[Test, Condition(assert: 'is_readable("/dev/urandom")')]
  public function urandom_int() {
    $random= (new Random(Random::URANDOM))->int(0, 10);
    Assert::true($random >= 0 && $random <= 10);
  }

  #[Test, Expect(IllegalArgumentException::class), Values([10, 11])]
  public function min_cannot_be_larger_or_equal_to_max($min) {
    (new Random())->int($min, 10);
  }

  #[Test, Expect(IllegalArgumentException::class), Condition(assert: '0x7FFFFFFF === PHP_INT_MAX')]
  public function max_cannot_be_larger_than_int_max() {
    (new Random())->int(0, PHP_INT_MAX + 1);
  }

  #[Test, Expect(IllegalArgumentException::class), Condition(assert: '0x7FFFFFFF === PHP_INT_MAX')]
  public function min_cannot_be_smaller_than_int_min() {
    (new Random())->int(PHP_INT_MIN - 1, 0);
  }
}