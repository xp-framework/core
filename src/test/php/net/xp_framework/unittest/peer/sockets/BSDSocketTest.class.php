<?php namespace net\xp_framework\unittest\peer\sockets;

use unittest\actions\ExtensionAvailable;
use unittest\actions\Actions;
use net\xp_framework\unittest\StartServer;
use peer\BSDSocket;
use lang\IllegalStateException;

/**
 * TestCase
 *
 * @ext      sockets
 * @see      xp://peer.BSDSocket
 */
#[@action([
#  new ExtensionAvailable('sockets'),
#  new StartServer('net.xp_framework.unittest.peer.sockets.TestingServer', 'connected', 'shutdown')
#])]
class BSDSocketTest extends AbstractSocketTest {

  /**
   * Creates a new client socket
   *
   * @param   string addr
   * @param   int port
   * @return  peer.Socket
   */
  protected function newSocket($addr, $port) {
    return new BSDSocket($addr, $port);
  }
  
  #[@test]
  public function inetDomain() {
    $this->fixture->setDomain(AF_INET);
    $this->assertEquals(AF_INET, $this->fixture->getDomain());
  }

  #[@test]
  public function unixDomain() {
    $this->fixture->setDomain(AF_UNIX);
    $this->assertEquals(AF_UNIX, $this->fixture->getDomain());
  }

  #[@test, @expect(IllegalStateException::class)]
  public function setDomainOnConnected() {
    $this->fixture->connect();
    $this->fixture->setDomain(AF_UNIX);
  }

  #[@test]
  public function streamType() {
    $this->fixture->setType(SOCK_STREAM);
    $this->assertEquals(SOCK_STREAM, $this->fixture->getType());
  }

  #[@test]
  public function dgramType() {
    $this->fixture->setType(SOCK_DGRAM);
    $this->assertEquals(SOCK_DGRAM, $this->fixture->getType());
  }

  #[@test, @expect(IllegalStateException::class)]
  public function setTypeOnConnected() {
    $this->fixture->connect();
    $this->fixture->setType(SOCK_STREAM);
  }

  #[@test]
  public function tcpProtocol() {
    $this->fixture->setProtocol(SOL_TCP);
    $this->assertEquals(SOL_TCP, $this->fixture->getProtocol());
  }

  #[@test]
  public function udpProtocol() {
    $this->fixture->setProtocol(SOL_UDP);
    $this->assertEquals(SOL_UDP, $this->fixture->getProtocol());
  }

  #[@test, @expect(IllegalStateException::class)]
  public function setProtocolOnConnected() {
    $this->fixture->connect();
    $this->fixture->setProtocol(SOL_TCP);
  }
}
