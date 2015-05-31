<?php namespace net\xp_framework\unittest\peer\sockets;

use peer\SocketTimeoutException;

class SocketTimeoutExceptionTest extends \unittest\TestCase {

  #[@test]
  public function getTimeout() {
    $this->assertEquals(
      1.0, 
      (new SocketTimeoutException('', 1.0))->getTimeout()
    );
  }

  #[@test]
  public function compoundMessage() {
    $this->assertEquals(
      'Exception peer.SocketTimeoutException (Read failed after 1.000 seconds)',
      (new SocketTimeoutException('Read failed', 1.0))->compoundMessage()
    );
  }
}
