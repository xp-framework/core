<?php namespace net\xp_framework\unittest\peer\net;

use peer\net\Inet4Address;
use peer\net\Inet6Address;
use peer\net\NameserverLookup;
use lang\ElementNotFoundException;

/**
 * Test nameserver lookup API
 *
 * @see   xp://peer.net.NameserverLookup'
 */
class NameserverLookupTest extends \unittest\TestCase {
  private $cut;

  /**
   * Sets up test case and defines dummy nameserver lookup fixture
   *
   * @return void
   */
  public function setUp() {
    $this->cut= newinstance(NameserverLookup::class, [], [
      'results' => [],
      'addLookup' => function($ip, $type= 'ip') { $this->results[]= [$type => $ip]; },
      '_nativeLookup' => function($what, $type) { return $this->results; }
    ]);
  }

  #[@test]
  public function lookupLocalhostAllInet4() {
    $this->cut->addLookup('127.0.0.1');
    $this->assertEquals([new Inet4Address('127.0.0.1')], $this->cut->lookupAllInet4('localhost'));
  }

  #[@test]
  public function lookupLocalhostInet4() {
    $this->cut->addLookup('127.0.0.1');
    $this->assertEquals(new Inet4Address('127.0.0.1'), $this->cut->lookupInet4('localhost'));
  }

  #[@test]
  public function lookupLocalhostAllInet6() {
    $this->cut->addLookup('::1', 'ipv6');
    $this->assertEquals([new Inet6Address('::1')], $this->cut->lookupAllInet6('localhost'));
  }

  #[@test]
  public function lookupLocalhostInet6() {
    $this->cut->addLookup('::1', 'ipv6');
    $this->assertEquals(new Inet6Address('::1'), $this->cut->lookupInet6('localhost'));
  }

  #[@test]
  public function lookupLocalhostAll() {
    $this->cut->addLookup('127.0.0.1');
    $this->cut->addLookup('::1', 'ipv6');
    
    $this->assertEquals(
      [new Inet4Address('127.0.0.1'), new Inet6Address('::1')],
      $this->cut->lookupAll('localhost')
    );
  }

  #[@test]
  public function lookupLocalhost() {
    $this->cut->addLookup('127.0.0.1');
    $this->cut->addLookup('::1', 'ipv6');

    $this->assertEquals(
      new Inet4Address('127.0.0.1'),
      $this->cut->lookup('localhost')
    );
  }

  #[@test]
  public function lookupAllNonexistantGivesEmptyArray() {
    $this->assertEquals([], $this->cut->lookupAll('localhost'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function lookupNonexistantThrowsException() {
    $this->cut->lookup('localhost');
  }

  #[@test]
  public function reverseLookup() {
    $this->cut->addLookup('localhost', 'target');
    $this->assertEquals('localhost', $this->cut->reverseLookup(new Inet4Address('127.0.0.1')));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function nonexistingReverseLookupCausesException() {
    $this->cut->reverseLookup(new Inet4Address('192.168.1.1'));
  }

  #[@test]
  public function tryReverseLookupReturnsNullWhenNoneFound() {
    $this->assertNull($this->cut->tryReverseLookup(new Inet4Address('192.178.1.1')));
  }
}
