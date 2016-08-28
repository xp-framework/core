<?php namespace net\xp_framework\unittest\util\cmd;

use util\cmd\Console;
use lang\{Object, IllegalStateException};
use io\streams\{MemoryInputStream, MemoryOutputStream, ConsoleOutputStream, ConsoleInputStream};

/**
 * TestCase for the Console class
 */
class ConsoleTest extends \unittest\TestCase {
  protected $original, $streams;

  /**
   * Sets up test case. Redirects console standard output/error streams
   * to memory streams
   */
  public function setUp() {
    $this->original= [Console::$in->getStream(), Console::$out->getStream(), Console::$err->getStream()];
    $this->streams= [null, new MemoryOutputStream(), new MemoryOutputStream()];
    Console::$out->setStream($this->streams[1]);
    Console::$err->setStream($this->streams[2]);
  }
  
  /**
   * Tear down testcase. Restores original standard output/error streams
   */
  public function tearDown() {
    Console::$in->setStream($this->original[0]);
    Console::$out->setStream($this->original[1]);
    Console::$err->setStream($this->original[2]);
  }

  #[@test]
  public function read() {
    Console::$in->setStream(new MemoryInputStream('.'));
    $this->assertEquals('.', Console::read());
  }

  #[@test, @values([
  #  "Hello\nHallo",
  #  "Hello\rHallo",
  #  "Hello\r\nHallo",
  #])]
  public function readLine($variation) {
    Console::$in->setStream(new MemoryInputStream($variation));
    $this->assertEquals('Hello', Console::readLine());
    $this->assertEquals('Hallo', Console::readLine());
  }

  #[@test]
  public function read_from_standard_input() {
    Console::$in->setStream(new MemoryInputStream('.'));
    $this->assertEquals('.', Console::$in->read(1));
  }
 
  #[@test]
  public function readLine_from_standard_input() {
    Console::$in->setStream(new MemoryInputStream("Hello\nHallo\nOla"));
    $this->assertEquals('Hello', Console::$in->readLine());
    $this->assertEquals('Hallo', Console::$in->readLine());
    $this->assertEquals('Ola', Console::$in->readLine());
  }
 
  #[@test]
  public function write() {
    Console::write('.');
    $this->assertEquals('.', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_multiple() {
    Console::write('.', 'o', 'O', '0');
    $this->assertEquals('.oO0', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_int() {
    Console::write(1);
    $this->assertEquals('1', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_true() {
    Console::write(true);
    $this->assertEquals('true', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_false() {
    Console::write(false);
    $this->assertEquals('false', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_float() {
    Console::write(1.5);
    $this->assertEquals('1.5', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_an_array() {
    Console::write([1, 2, 3]);
    $this->assertEquals('[1, 2, 3]', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_a_map() {
    Console::write(['key' => 'value', 'color' => 'blue']);
    $this->assertEquals(
      "[\n".
      "  key => \"value\"\n".
      "  color => \"blue\"\n".
      "]", 
      $this->streams[1]->getBytes()
    );
  }

  #[@test]
  public function write_an_object() {
    Console::write(new class() extends Object {
      public function toString() { return "Hello"; }
    });
    $this->assertEquals('Hello', $this->streams[1]->getBytes());
  }

  #[@test]
  public function exception_from_toString() {
    try {
      Console::write(new class() extends Object {
        public function toString() { throw new IllegalStateException("Cannot render string"); }
      });
      $this->fail('Expected exception not thrown', null, 'lang.IllegalStateException');
    } catch (IllegalStateException $expected) {
      $this->assertEquals('', $this->streams[1]->getBytes());
    }
  }

  #[@test]
  public function write_to_standard_out() {
    Console::$out->write('.');
    $this->assertEquals('.', $this->streams[1]->getBytes());
  }

  #[@test]
  public function write_to_standard_error() {
    Console::$err->write('.');
    $this->assertEquals('.', $this->streams[2]->getBytes());
  }

  #[@test]
  public function writef() {
    Console::writef('Hello "%s"', 'Timm');
    $this->assertEquals('Hello "Timm"', $this->streams[1]->getBytes());
  }

  #[@test]
  public function writef_to_standard_out() {
    Console::$out->writef('Hello "%s"', 'Timm');
    $this->assertEquals('Hello "Timm"', $this->streams[1]->getBytes());
  }

  #[@test]
  public function writef_to_standard_error() {
    Console::$err->writef('Hello "%s"', 'Timm');
    $this->assertEquals('Hello "Timm"', $this->streams[2]->getBytes());
  }

  #[@test]
  public function writeLine() {
    Console::writeLine('.');
    $this->assertEquals(".\n", $this->streams[1]->getBytes());
  }

  #[@test]
  public function writeLine_to_standard_out() {
    Console::$out->writeLine('.');
    $this->assertEquals(".\n", $this->streams[1]->getBytes());
  }

  #[@test]
  public function writeLine_to_standard_error() {
    Console::$err->writeLine('.');
    $this->assertEquals(".\n", $this->streams[2]->getBytes());
  }

  #[@test]
  public function writeLinef() {
    Console::writeLinef('Hello %s', 'World');
    $this->assertEquals("Hello World\n", $this->streams[1]->getBytes());
  }

  #[@test]
  public function writeLinef_to_standard_out() {
    Console::$out->writeLinef('Hello %s', 'World');
    $this->assertEquals("Hello World\n", $this->streams[1]->getBytes());
  }

  #[@test]
  public function writeLinef_to_standard_error() {
    Console::$err->writeLinef('Hello %s', 'World');
    $this->assertEquals("Hello World\n", $this->streams[2]->getBytes());
  }

  /**
   * Test initialization
   *
   * @param  bool console
   * @param  var assertions
   */
  protected function initialize($console, $assertions) {
    $in= Console::$in;
    $out= Console::$out;
    $err= Console::$err;
    Console::initialize($console);
    try {
      $assertions();
    } finally {
      Console::$in= $in;
      Console::$out= $out;
      Console::$err= $err;
    }
  }

  #[@test]
  public function initialize_on_console() {
    $this->initialize(true, function() {
      $this->assertInstanceOf(ConsoleInputStream::class, Console::$in->getStream());
      $this->assertInstanceOf(ConsoleOutputStream::class, Console::$out->getStream());
      $this->assertInstanceOf(ConsoleOutputStream::class, Console::$err->getStream());
    });
  }

  #[@test]
  public function initialize_without_console() {
    $this->initialize(false, function() {
      $this->assertEquals(null, Console::$in->getStream());
      $this->assertEquals(null, Console::$out->getStream());
      $this->assertEquals(null, Console::$err->getStream());
    });
  }
}
