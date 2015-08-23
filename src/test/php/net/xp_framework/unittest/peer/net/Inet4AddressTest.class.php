<?php namespace net\xp_framework\unittest\peer\net;

use peer\net\Inet4Address;
use peer\net\Network;
use lang\FormatException;

class Inet4AddressTest extends \unittest\TestCase {

  #[@test]
  public function createAddress() {
    $this->assertEquals('127.0.0.1', (new Inet4Address('127.0.0.1'))->asString());
  }

  #[@test, @expect(FormatException::class)]
  public function createInvalidAddressRaisesException() {
    new Inet4Address('Who am I');
  }

  #[@test, @expect(FormatException::class)]
  public function createInvalidAddressThatLooksLikeAddressRaisesException() {
    new Inet4Address('10.0.0.355');
  }
  
  #[@test, @expect(FormatException::class)]
  public function createInvalidAddressWithTooManyBlocksRaisesException() {
    new Inet4Address('10.0.0.255.5');
  }

  #[@test]
  public function loopbackAddress() {
    $this->assertTrue((new Inet4Address('127.0.0.1'))->isLoopback());
  }
  
  #[@test]
  public function alternativeLoopbackAddress() {
    $this->assertTrue((new Inet4Address('127.0.0.200'))->isLoopback());
  }
  
  #[@test]
  public function inSubnet() {
    $this->assertTrue((new Inet4Address('192.168.2.1'))->inSubnet(new Network(new Inet4Address('192.168.2'), 24)));
  }
  
  #[@test]
  public function notInSubnet() {
    $this->assertFalse((new Inet4Address('192.168.2.1'))->inSubnet(new Network(new Inet4Address('172.17.0.0'), 12)));
  }
  
  #[@test]
  public function hostInOwnHostSubnet() {
    $this->assertTrue((new Inet4Address('172.17.29.6'))->inSubnet(new Network(new Inet4Address('172.17.29.6'), 32)));
  }
  
  #[@test, @expect(FormatException::class)]
  public function illegalSubnet() {
    (new Inet4Address('172.17.29.6'))->inSubnet(new Network(new Inet4Address('172.17.29.6'), 33));
  }

  #[@test]
  public function sameIPsShouldBeEqual() {
    $this->assertEquals(new Inet4Address('127.0.0.1'), new Inet4Address('127.0.0.1'));
  }

  #[@test]
  public function differentIPsShouldBeDifferent() {
    $this->assertNotEquals(new Inet4Address('127.0.0.5'), new Inet4Address('192.168.1.1'));
  }

  #[@test]
  public function castToString() {
    $this->assertEquals('192.168.1.1', (string)new Inet4Address('192.168.1.1'));
  }

  #[@test]
  public function reverseNotationLocalhost() {
    $this->assertEquals('1.0.0.127.in-addr.arpa', (new Inet4Address('127.0.0.1'))->reversedNotation());
  }
  
  #[@test]
  public function createSubnet_creates_subnet_with_trailing_zeros() {
    $addr= new Inet4Address('192.168.1.1');
    $subNetSize= 24;
    $expAddr= new Inet4Address('192.168.1.0');
    $this->assertEquals($expAddr, $addr->createSubnet($subNetSize)->getAddress());
    
    $addr= new Inet4Address('192.168.1.1');
    $subNetSize= 12;
    $expAddr= new Inet4Address('192.160.0.0');
    $this->assertEquals($expAddr, $addr->createSubnet($subNetSize)->getAddress());
  }
}
