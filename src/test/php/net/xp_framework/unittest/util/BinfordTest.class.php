<?php namespace net\xp_framework\unittest\util;
 
use util\Binford;
use lang\IllegalArgumentException;

/**
 * Test Binford class
 *
 * @see  xp://util.Binford
 */
class BinfordTest extends \unittest\TestCase {
  protected static $observable;

  #[@test]
  public function can_create() {
    new Binford(6100);
  }

  #[@test]
  public function default_power_is_6100() {
    $this->assertEquals(new Binford(6100), new Binford());
  }

  #[@test]
  public function get_powered_by_returns_powerr() {
    $this->assertEquals(6100, (new Binford(6100))->getPoweredBy());
  }

  #[@test]
  public function set_powered_by_modifies_power() {
    $binford= new Binford(6100);
    $binford->setPoweredBy(61000);  // Hrhr, even more power!
    $this->assertEquals(61000, $binford->getPoweredBy());
  }

  #[@test]
  public function zero_power_allowed() {
    new Binford(0);
  }

  #[@test]
  public function fraction_0_61_power_allowed() {
    new Binford(0.61);
  }

  #[@test]
  public function fraction_6_1_power_allowed() {
    new Binford(6.1);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function non_binford_number_not_allowed() {
    new Binford(6200);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function double_binford_number_not_allowed() {
    new Binford(6100 * 2);
  }

  #[@test]
  public function string_representation() {
    $this->assertEquals('util.Binford(6100)', (new Binford(6100))->toString());
  }

  #[@test]
  public function header_representation() {
    $this->assertEquals(
      new \peer\Header('X-Binford', '6100 (more power)'),
      (new Binford(6100))->getHeader()
    );
  }
}
