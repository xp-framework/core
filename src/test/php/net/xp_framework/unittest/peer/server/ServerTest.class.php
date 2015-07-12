<?php namespace net\xp_framework\unittest\peer\server;

/**
 * TestCase for default server protocol
 *
 */
class ServerTest extends AbstractServerTest {
  
  /**
   * Starts server in background
   *
   * @return void
   */
  #[@beforeClass]
  public static function startServer() {
    parent::startServerWith('net.xp_framework.unittest.peer.server.TestingProtocol');
  }

  #[@test]
  public function connected() {
    $this->connect();
    $this->assertHandled(['CONNECT']);
  }

  #[@test]
  public function disconnected() {
    $this->connect();
    $this->conn->close();
    $this->assertHandled(['CONNECT', 'DISCONNECT']);
  }

  #[@test, @ignore('Fragile test, dependant on OS / platform and implementation vagaries')]
  public function error() {
    $this->connect();
    $this->conn->write("SEND\n");
    $this->conn->close();
    $this->assertHandled(['CONNECT', 'ERROR']);
  }
}
