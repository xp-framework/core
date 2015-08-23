<?php namespace net\xp_framework\unittest\peer\net;

use unittest\TestCase;
use peer\net\InetAddressFactory;
use peer\net\Inet4Address;
use peer\net\Inet6Address;
use lang\FormatException;

class InetAddressFactoryTest extends TestCase {
  private $cut;

  /** @return void */
  public function setUp() {
    $this->cut= new InetAddressFactory();
  }

  #[@test]
  public function createLocalhostV4() {
    $this->assertInstanceOf(Inet4Address::class, $this->cut->parse('127.0.0.1'));
  }

  #[@test, @expect(FormatException::class)]
  public function parseInvalidAddressThatLooksLikeV4() {
    $this->cut->parse('3.33.333.333');
  }

  #[@test, @expect(FormatException::class)]
  public function parseInvalidAddressThatAlsoLooksLikeV4() {
    $this->cut->parse('10..3.3');
  }

  #[@test]
  public function tryParse() {
    $this->assertEquals(new Inet4Address('172.17.29.6'), $this->cut->tryParse('172.17.29.6'));
  }

  #[@test]
  public function tryParseReturnsNullOnFailure() {
    $this->assertEquals(null, $this->cut->tryParse('not an ip address'));
  }

  #[@test]
  public function parseLocalhostV6() {
    $this->assertInstanceOf(Inet6Address::class, $this->cut->parse('::1'));
  }

  #[@test]
  public function parseV6() {
    $this->assertInstanceOf(Inet6Address::class, $this->cut->parse('fe80::a6ba:dbff:fefe:7755'));
  }

  #[@test, @expect(FormatException::class)]
  public function parseThatLooksLikeV6() {
    $this->cut->parse('::ffffff:::::a');
  }
}
