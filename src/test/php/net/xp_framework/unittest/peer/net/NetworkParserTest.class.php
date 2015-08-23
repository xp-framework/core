<?php namespace net\xp_framework\unittest\peer\net;

use peer\net\NetworkParser;
use peer\net\Network;
use peer\net\Inet4Address;
use peer\net\Inet6Address;
use lang\FormatException;

class NetworkParserTest extends \unittest\TestCase {
  private $cut;

  /** @return void */
  public function setUp() {
    $this->cut= new NetworkParser();
  }

  #[@test]
  public function parseV4Network() {
    $this->assertEquals(
      new Network(new Inet4Address('192.168.1.1'), 24),
      $this->cut->parse('192.168.1.1/24')
    );
  }

  #[@test, @expect(FormatException::class)]
  public function parseV4NetworkThrowsExceptionOnIllegalNetworkString() {
    $this->cut->parse('192.168.1.1 b24');
  }

  #[@test]
  public function parseV6Network() {
    $this->assertEquals(
      new Network(new Inet6Address('fc00::'), 7),
      $this->cut->parse('fc00::/7')
    );
  }

  #[@test]
  public function tryParse() {
    $this->assertEquals(
      new Network(new Inet4Address('172.16.0.0'), 12),
      $this->cut->tryParse('172.16.0.0/12')
    );
  }

  #[@test]
  public function tryParseReturnsNullOnFailure() {
    $this->assertEquals(null, $this->cut->tryParse('not a network'));
  }
}
