<?php namespace net\xp_framework\unittest\util\cmd;

use io\streams\{MemoryInputStream, MemoryOutputStream, ConsoleOutputStream, ConsoleInputStream};
use lang\{Value, IllegalStateException};
use util\cmd\{Console, NoInput, NoOutput};

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
    $this->original= [Console::$in->stream(), Console::$out->stream(), Console::$err->stream()];
    $this->streams= [null, new MemoryOutputStream(), new MemoryOutputStream()];
    Console::$out->redirect($this->streams[1]);
    Console::$err->redirect($this->streams[2]);
  }
  
  /**
   * Tear down testcase. Restores original standard output/error streams
   */
  public function tearDown() {
    Console::$in->redirect($this->original[0]);
    Console::$out->redirect($this->original[1]);
    Console::$err->redirect($this->original[2]);
  }

  #[@test]
  public function read() {
    Console::$in->redirect(new MemoryInputStream('.'));
    $this->assertEquals('.', Console::read());
  }

  #[@test, @values([
  #  "Hello\nHallo",
  #  "Hello\rHallo",
  #  "Hello\r\nHallo",
  #])]
  public function readLine($variation) {
    Console::$in->redirect(new MemoryInputStream($variation));
    $this->assertEquals('Hello', Console::readLine());
    $this->assertEquals('Hallo', Console::readLine());
  }

  #[@test]
  public function read_from_standard_input() {
    Console::$in->redirect(new MemoryInputStream('.'));
    $this->assertEquals('.', Console::$in->read(1));
  }
 
  #[@test]
  public function readLine_from_standard_input() {
    Console::$in->redirect(new MemoryInputStream("Hello\nHallo\nOla"));
    $this->assertEquals('Hello', Console::$in->readLine());
    $this->assertEquals('Hallo', Console::$in->readLine());
    $this->assertEquals('Ola', Console::$in->readLine());
  }
 
  #[@test]
  public function write() {
    Console::write('.');
    $this->assertEquals('.', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_multiple() {
    Console::write('.', 'o', 'O', '0');
    $this->assertEquals('.oO0', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_int() {
    Console::write(1);
    $this->assertEquals('1', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_true() {
    Console::write(true);
    $this->assertEquals('true', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_false() {
    Console::write(false);
    $this->assertEquals('false', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_float() {
    Console::write(1.5);
    $this->assertEquals('1.5', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_an_array() {
    Console::write([1, 2, 3]);
    $this->assertEquals('[1, 2, 3]', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_a_map() {
    Console::write(['key' => 'value', 'color' => 'blue']);
    $this->assertEquals(
      "[\n".
      "  key => \"value\"\n".
      "  color => \"blue\"\n".
      "]", 
      $this->streams[1]->bytes()
    );
  }

  #[@test]
  public function write_an_object() {
    Console::write(new class() implements Value {
      public function toString() { return 'Hello'; }
      public function hashCode() { return get_class($this); }
      public function compareTo($value) { return 1; }
    });
    $this->assertEquals('Hello', $this->streams[1]->bytes());
  }

  #[@test]
  public function exception_from_toString() {
    try {
      Console::write(new class() implements Value {
        public function toString() { throw new IllegalStateException('Cannot render string'); }
        public function hashCode() { return get_class($this); }
        public function compareTo($value) { return 1; }
      });
      $this->fail('Expected exception not thrown', null, 'lang.IllegalStateException');
    } catch (IllegalStateException $expected) {
      $this->assertEquals('', $this->streams[1]->bytes());
    }
  }

  #[@test]
  public function write_to_standard_out() {
    Console::$out->write('.');
    $this->assertEquals('.', $this->streams[1]->bytes());
  }

  #[@test]
  public function write_to_standard_error() {
    Console::$err->write('.');
    $this->assertEquals('.', $this->streams[2]->bytes());
  }

  #[@test]
  public function writef() {
    Console::writef('Hello "%s"', 'Timm');
    $this->assertEquals('Hello "Timm"', $this->streams[1]->bytes());
  }

  #[@test]
  public function writef_to_standard_out() {
    Console::$out->writef('Hello "%s"', 'Timm');
    $this->assertEquals('Hello "Timm"', $this->streams[1]->bytes());
  }

  #[@test]
  public function writef_to_standard_error() {
    Console::$err->writef('Hello "%s"', 'Timm');
    $this->assertEquals('Hello "Timm"', $this->streams[2]->bytes());
  }

  #[@test]
  public function writeLine() {
    Console::writeLine('.');
    $this->assertEquals(".\n", $this->streams[1]->bytes());
  }

  #[@test]
  public function writeLine_to_standard_out() {
    Console::$out->writeLine('.');
    $this->assertEquals(".\n", $this->streams[1]->bytes());
  }

  #[@test]
  public function writeLine_to_standard_error() {
    Console::$err->writeLine('.');
    $this->assertEquals(".\n", $this->streams[2]->bytes());
  }

  #[@test]
  public function writeLinef() {
    Console::writeLinef('Hello %s', 'World');
    $this->assertEquals("Hello World\n", $this->streams[1]->bytes());
  }

  #[@test]
  public function writeLinef_to_standard_out() {
    Console::$out->writeLinef('Hello %s', 'World');
    $this->assertEquals("Hello World\n", $this->streams[1]->bytes());
  }

  #[@test]
  public function writeLinef_to_standard_error() {
    Console::$err->writeLinef('Hello %s', 'World');
    $this->assertEquals("Hello World\n", $this->streams[2]->bytes());
  }

  /**
   * Test initialization
   *
   * @param  bool $console
   * @param  function(): void $assertions
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
      $this->assertInstanceOf(ConsoleInputStream::class, Console::$in->stream());
      $this->assertInstanceOf(ConsoleOutputStream::class, Console::$out->stream());
      $this->assertInstanceOf(ConsoleOutputStream::class, Console::$err->stream());
    });
  }

  #[@test]
  public function initialize_without_console() {
    $this->initialize(false, function() {
      $this->assertNull(Console::$in->stream());
      $this->assertNull(Console::$out->stream());
      $this->assertNull(Console::$err->stream());
    });
  }
}
