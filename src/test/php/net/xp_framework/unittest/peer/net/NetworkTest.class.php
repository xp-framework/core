<?php namespace net\xp_framework\unittest\peer\net;

use peer\net\Network;
use peer\net\Inet4Address;
use peer\net\Inet6Address;
use lang\FormatException;

class NetworkTest extends \unittest\TestCase {

  #[@test]
  public function createNetwork() {
    $net= new Network(new Inet4Address("127.0.0.1"), 24);
    $this->assertEquals('127.0.0.1/24', $net->asString());
  }

  #[@test, @expect(FormatException::class)]
  public function createNetworkFailsIfTooLargeNetmaskGiven() {
    new Network(new Inet4Address("127.0.0.1"), 33);
  }

  #[@test]
  public function createNetworkV6() {
    $this->assertEquals(
      'fe00::/7',
      (new Network(new Inet6Address('fe00::'), 7))->asString()
    );
  }

  #[@test]
  public function createNetworkV6WorkAlsoWithNetmaskTooBigInV4() {
    $this->assertEquals(
      'fe00::/35',
      (new Network(new Inet6Address('fe00::'), 35))->asString()
    );
  }

  #[@test, @expect(FormatException::class)]
  public function createNetworkV6FailsIfTooLargeNetmaskGiven() {
    new Network(new Inet6Address('fe00::'), 763);
  }

  #[@test, @expect(FormatException::class)]
  public function createNetworkFailsIfTooSmallNetmaskGiven() {
    new Network(new Inet4Address("127.0.0.1"), -1);
  }

  #[@test, @expect(FormatException::class)]
  public function createNetworkFailsIfNonIntegerNetmaskGiven() {
    new Network(new Inet4Address("127.0.0.1"), 0.5);
  }

  #[@test, @expect(FormatException::class)]
  public function createNetworkFailsIfStringGiven() {
    new Network(new Inet4Address("127.0.0.1"), "Hello");
  }

  #[@test]
  public function networkAddress() {
    $net= new Network(new Inet4Address("127.0.0.0"), 24);
    $this->assertEquals(new Inet4Address("127.0.0.0"), $net->getNetworkAddress());
  }

  #[@test]
  public function loopbackNetworkContainsLoopbackAddressV4() {
    $this->assertTrue((new Network(new Inet4Address('127.0.0.5'), 24))->contains(new Inet4Address('127.0.0.1')));
  }

  #[@test]
  public function equalNetworksAreEqual() {
    $this->assertEquals(
      new Network(new Inet4Address('127.0.0.1'), 8),
      new Network(new Inet4Address('127.0.0.1'), 8)
    );
  }
}
