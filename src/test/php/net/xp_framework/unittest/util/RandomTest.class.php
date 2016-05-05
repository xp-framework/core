<?php namespace net\xp_framework\unittest\util;

use util\Random;
use unittest\actions\ExtensionAvailable;
use unittest\actions\VerifyThat;
use lang\IllegalArgumentException;

class RandomTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new Random();
  }

  #[@test]
  public function bytes() {
    $this->assertEquals(20, (new Random())->bytes(20)->size());
  }

  #[@test]
  public function default_bytes() {
    $this->assertEquals(20, (new Random(Random::BEST))->bytes(20)->size());
  }

  #[@test, @action(new ExtensionAvailable('openssl'))]
  public function openssl_bytes() {
    $this->assertEquals(20, (new Random(Random::OPENSSL))->bytes(20)->size());
  }

  #[@test, @action(new ExtensionAvailable('mcrypt'))]
  public function mcrypt_bytes() {
    $this->assertEquals(20, (new Random(Random::MCRYPT))->bytes(20)->size());
  }

  #[@test, @action(new VerifyThat(function() { return is_readable('/dev/urandom'); }))]
  public function urandom_bytes() {
    $this->assertEquals(20, (new Random(Random::URANDOM))->bytes(20)->size());
  }

  #[@test]
  public function mtrand_bytes() {
    $this->assertEquals(20, (new Random(Random::MTRAND))->bytes(20)->size());
  }

  #[@test, @expect(IllegalArgumentException::class), @values([-1, 0])]
  public function cannot_use_limit_smaller_than_one($limit) {
    (new Random())->bytes($limit);
  }

  #[@test]
  public function int() {
    $random= (new Random())->int(0, 10);
    $this->assertTrue($random >= 0 && $random <= 10);
  }

  #[@test]
  public function negative_int() {
    $random= (new Random())->int(-10, -1);
    $this->assertTrue($random >= -10 && $random <= -1);
  }

  #[@test]
  public function limits_default_to_zero_to_int_max() {
    $random= (new Random())->int();
    $this->assertTrue($random >= 0 && $random <= PHP_INT_MAX);
  }

  #[@test, @action(new ExtensionAvailable('openssl'))]
  public function openssl_int() {
    $random= (new Random(Random::OPENSSL))->int(0, 10);
    $this->assertTrue($random >= 0 && $random <= 10);
  }

  #[@test, @action(new ExtensionAvailable('mcrypt'))]
  public function mcrypt_int() {
    $random= (new Random(Random::MCRYPT))->int(0, 10);
    $this->assertTrue($random >= 0 && $random <= 10);
  }

  #[@test, @action(new VerifyThat(function() { return is_readable('/dev/urandom'); }))]
  public function urandom_int() {
    $random= (new Random(Random::URANDOM))->int(0, 10);
    $this->assertTrue($random >= 0 && $random <= 10);
  }

  #[@test]
  public function mtrand_int() {
    $random= (new Random(Random::MTRAND))->int(0, 10);
    $this->assertTrue($random >= 0 && $random <= 10);
  }

  #[@test, @expect(IllegalArgumentException::class), @values([10, 11])]
  public function min_cannot_be_larger_or_equal_to_max($min) {
    (new Random())->int($min, 10);
  }
}