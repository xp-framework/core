<?php namespace net\xp_framework\unittest\peer\sockets;

use peer\Socket;
use peer\SocketEndpoint;
use peer\SocketException;
use peer\ConnectException;
use peer\SocketTimeoutException;
use lang\Runtime;

abstract class AbstractSocketTest extends \unittest\TestCase {
  protected static $bindAddress= [null, -1];
  protected $fixture= null;

  /**
   * Callback for when server is connected
   *
   * @param  string $bindAddress
   * @return vid
   */
  public static function connected($bindAddress) {
    self::$bindAddress= explode(':', $bindAddress);
  }

  /**
   * Callback for when server should be shut down
   *
   * @return vid
   */
  public static function shutdown() {
    $c= new Socket(self::$bindAddress[0], self::$bindAddress[1]);
    $c->connect();
    $c->write("HALT\n");
    $c->close();
  }
  
  /**
   * Creates a new client socket
   *
   * @param   string addr
   * @param   int port
   * @return  peer.Socket
   */
  protected abstract function newSocket($addr, $port);

  /**
   * Read exactly the specific amount of bytes.
   *
   * @param   int num
   * @return  string
   */
  protected function readBytes($num) {
    $bytes= '';
    do {
      $bytes.= $this->fixture->readBinary($num- strlen($bytes));
    } while (strlen($bytes) < $num);
    return $bytes;
  }


  /** @return void */
  public function setUp() {
    $this->fixture= $this->newSocket(self::$bindAddress[0], self::$bindAddress[1]);
  }

  /** @return void */
  public function tearDown() {
    $this->fixture->isConnected() && $this->fixture->close();
  }
  
  #[@test]
  public function initiallyNotConnected() {
    $this->assertFalse($this->fixture->isConnected());
  }

  #[@test]
  public function connect() {
    $this->assertTrue($this->fixture->connect());
    $this->assertTrue($this->fixture->isConnected());
  }

  #[@test, @expect(ConnectException::class)]
  public function connectInvalidPort() {
    $this->newSocket(self::$bindAddress[0], -1)->connect(0.1);
  }

  #[@test, @expect(ConnectException::class)]
  public function connectInvalidHost() {
    $this->newSocket('@invalid', self::$bindAddress[1])->connect(0.1);
  }

  #[@test, @expect(ConnectException::class)]
  public function connectIANAReserved49151() {
    $this->newSocket(self::$bindAddress[0], 49151)->connect(0.1);
  }

  #[@test]
  public function closing() {
    $this->assertTrue($this->fixture->connect());
    $this->assertTrue($this->fixture->close());
    $this->assertFalse($this->fixture->isConnected());
  }

  #[@test]
  public function closingNotConnected() {
    $this->assertFalse($this->fixture->close());
  }
  
  #[@test]
  public function eofAfterClosing() {
    $this->assertTrue($this->fixture->connect());
    
    $this->fixture->write("ECHO EOF\n");
    $this->assertEquals("+ECHO EOF\n", $this->fixture->readBinary());
    
    $this->fixture->write("CLOS\n");
    $this->assertEquals('', $this->fixture->readBinary());

    $this->assertTrue($this->fixture->eof());
    $this->fixture->close();
    $this->assertFalse($this->fixture->eof());
  }

  #[@test]
  public function write() {
    $this->fixture->connect();
    $this->assertEquals(10, $this->fixture->write("ECHO data\n"));
  }

  #[@test, @expect(SocketException::class)]
  public function writeUnConnected() {
    $this->fixture->write('Anything');
  }

  #[@test, @ignore('Writes still succeed after close - no idea why...')]
  public function writeAfterEof() {
    $this->fixture->connect();
    $this->fixture->write("CLOS\n");
    try {
      $this->fixture->write('Anything');
      $this->fail('No exception raised', null, 'peer.SocketException');
    } catch (SocketException $expected) {
      // OK
    }
  }

  #[@test]
  public function readLine() {
    $this->fixture->connect();
    $this->fixture->write("ECHO data\n");
    $this->assertEquals("+ECHO data", $this->fixture->readLine());
  }

  #[@test, @expect(SocketException::class)]
  public function readLineUnConnected() {
    $this->fixture->readLine();
  }

  #[@test]
  public function readLineOnEof() {
    $this->fixture->connect();
    $this->fixture->write("CLOS\n");
    $this->assertNull($this->fixture->readLine());
    $this->assertTrue($this->fixture->eof(), '<EOF>');
  }

  #[@test]
  public function readLinesWithLineFeed() {
    $this->fixture->connect();
    $this->fixture->write("LINE 5 %0A\n");
    for ($i= 0; $i < 5; $i++) {
      $this->assertEquals('+LINE '.$i, $this->fixture->readLine(), 'Line #'.$i);
    }
    $this->assertEquals('+LINE .', $this->fixture->readLine());
  }

  #[@test, @ignore('readLine() only works for \n or \r\n at the moment')]
  public function readLinesWithCarriageReturn() {
    $this->fixture->connect();
    $this->fixture->write("LINE 5 %0D\n");
    for ($i= 0; $i < 5; $i++) {
      $this->assertEquals('+LINE '.$i, $this->fixture->readLine(), 'Line #'.$i);
    }
    $this->assertEquals('+LINE .', $this->fixture->readLine());
  }

  #[@test]
  public function readLinesWithCarriageReturnLineFeed() {
    $this->fixture->connect();
    $this->fixture->write("LINE 5 %0D%0A\n");
    for ($i= 0; $i < 5; $i++) {
      $this->assertEquals('+LINE '.$i, $this->fixture->readLine(), 'Line #'.$i);
    }
    $this->assertEquals('+LINE .', $this->fixture->readLine());
  }
  
  #[@test]
  public function readLineAndBinary() {
    $this->fixture->connect();
    $this->fixture->write("LINE 3 %0D%0A\n");
    $this->assertEquals('+LINE 0', $this->fixture->readLine());
    $this->assertEquals("+LINE 1\r\n+LINE 2\r\n+LINE .\n", $this->readBytes(26));
  }

  #[@test]
  public function readLineAndBinaryWithMaxLen() {
    $this->fixture->connect();
    $this->fixture->write("LINE 3 %0D%0A\n");
    $this->assertEquals('+LINE 0', $this->fixture->readLine());
    $this->assertEquals("+LINE 1\r\n", $this->readBytes(9));
    $this->assertEquals("+LINE 2\r\n", $this->readBytes(9));
    $this->assertEquals('+LINE .', $this->fixture->readLine());
  }

  #[@test]
  public function read() {
    $this->fixture->connect();
    $this->fixture->write("ECHO data\n");
    $this->assertEquals("+ECHO data\n", $this->fixture->read());
  }

  #[@test, @expect(SocketException::class)]
  public function readUnConnected() {
    $this->fixture->read();
  }

  #[@test]
  public function readOnEof() {
    $this->fixture->connect();
    $this->fixture->write("CLOS\n");
    $this->assertNull($this->fixture->read());
    $this->assertTrue($this->fixture->eof(), '<EOF>');
  }

  #[@test]
  public function readBinary() {
    $this->fixture->connect();
    $this->fixture->write("ECHO data\n");
    $this->assertEquals("+ECHO data\n", $this->fixture->read());
  }

  #[@test, @expect(SocketException::class)]
  public function readBinaryUnConnected() {
    $this->fixture->readBinary();
  }

  #[@test]
  public function readBinaryOnEof() {
    $this->fixture->connect();
    $this->fixture->write("CLOS\n");
    $this->assertEquals('', $this->fixture->readBinary());
    $this->assertTrue($this->fixture->eof(), '<EOF>');
  }

  #[@test]
  public function canRead() {
    $this->fixture->connect();
    $this->assertFalse($this->fixture->canRead(0.1));
  }

  #[@test, @expect(SocketException::class)]
  public function canReadUnConnected() {
    $this->fixture->canRead(0.1);
  }

  #[@test]
  public function canReadWithData() {
    $this->fixture->connect();
    $this->fixture->write("ECHO data\n");
    $this->assertTrue($this->fixture->canRead(0.1));
  }

  #[@test]
  public function getHandle() {
    $this->fixture->connect();
    $this->assertTrue(is_resource($this->fixture->getHandle()));
  }

  #[@test]
  public function getHandleAfterClose() {
    $this->fixture->connect();
    $this->fixture->close();
    $this->assertNull($this->fixture->getHandle());
  }

  #[@test]
  public function getHandleUnConnected() {
    $this->assertNull($this->fixture->getHandle());
  }

  #[@test, @expect(SocketTimeoutException::class)]
  public function readTimeout() {
    $this->fixture->connect();
    $this->fixture->setTimeout(0.1);
    $this->fixture->read();
  }

  #[@test, @expect(SocketTimeoutException::class)]
  public function readBinaryTimeout() {
    $this->fixture->connect();
    $this->fixture->setTimeout(0.1);
    $this->fixture->readBinary();
  }

  #[@test, @expect(SocketTimeoutException::class)]
  public function readLineTimeout() {
    $this->fixture->connect();
    $this->fixture->setTimeout(0.1);
    $this->fixture->readLine();
  }

  #[@test]
  public function inputStream() {
    $expect= '<response><type>status</type><payload><bool>true</bool></payload></response>';
    $this->fixture->connect();
    $this->fixture->write('ECHO '.$expect."\n");
    
    $si= $this->fixture->getInputStream();
    $this->assertTrue($si->available() > 0, 'available() > 0');
    $this->assertEquals('+ECHO '.$expect, $si->read(strlen($expect)+ strlen('+ECHO ')));
  }

  #[@test]
  public function remoteEndpoint() {
    $this->assertEquals(
      new SocketEndpoint(self::$bindAddress[0], self::$bindAddress[1]),
      $this->fixture->remoteEndpoint()
    );
  }

  #[@test]
  public function localEndpointForUnconnectedSocket() {
    $this->assertNull($this->fixture->localEndpoint());
  }

  #[@test]
  public function localEndpointForConnectedSocket() {
    $this->fixture->connect();
    $this->assertInstanceOf(SocketEndpoint::class, $this->fixture->localEndpoint());
  }
}
