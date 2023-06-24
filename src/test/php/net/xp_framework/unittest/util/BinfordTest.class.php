<?php namespace net\xp_framework\unittest\util;

use lang\IllegalArgumentException;
use unittest\Assert;
use unittest\{Expect, Test, TestCase};
use util\Binford;

/**
 * Test Binford class
 *
 * @see  xp://util.Binford
 */
class BinfordTest {
  protected static $observable;

  #[Test]
  public function can_create() {
    new Binford(6100);
  }

  #[Test]
  public function default_power_is_6100() {
    Assert::equals(new Binford(6100), new Binford());
  }

  #[Test]
  public function get_powered_by_returns_powerr() {
    Assert::equals(6100, (new Binford(6100))->getPoweredBy());
  }

  #[Test]
  public function set_powered_by_modifies_power() {
    $binford= new Binford(6100);
    $binford->setPoweredBy(61000);  // Hrhr, even more power!
    Assert::equals(61000, $binford->getPoweredBy());
  }

  #[Test]
  public function zero_power_allowed() {
    new Binford(0);
  }

  #[Test]
  public function fraction_0_61_power_allowed() {
    new Binford(0.61);
  }

  #[Test]
  public function fraction_6_1_power_allowed() {
    new Binford(6.1);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function non_binford_number_not_allowed() {
    new Binford(6200);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function double_binford_number_not_allowed() {
    new Binford(6100 * 2);
  }

  #[Test]
  public function string_representation() {
    Assert::equals('util.Binford(6100)', (new Binford(6100))->toString());
  }
}