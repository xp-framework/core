<?php namespace net\xp_framework\unittest\util\cmd;

use io\streams\{ConsoleInputStream, ConsoleOutputStream, MemoryInputStream, MemoryOutputStream};
use lang\{IllegalStateException, Value};
use unittest\{Assert, Test, Values};
use util\cmd\{Console, NoInput, NoOutput};

class ConsoleTest {

  /**
   * Runs test
   *
   * @param  function(): void $test
   * @return void
   */
  private function run($test) {
    $original= [Console::$in->stream(), Console::$out->stream(), Console::$err->stream()];
    $streams= [null, new MemoryOutputStream(), new MemoryOutputStream()];
    Console::$out->redirect($streams[1]);
    Console::$err->redirect($streams[2]);

    try {
      $test();
    } finally {
      Console::$in->redirect($original[0]);
      Console::$out->redirect($original[1]);
      Console::$err->redirect($original[2]);
    }
  }

  /**
   * Test initialization
   *
   * @param  bool $console
   * @param  function(): void $assertions
   * @return void
   */
  private function initialize($console, $assertions) {
    $in= Console::$in;
    $out= Console::$out;
    $err= Console::$err;

    try {
      Console::initialize($console);
      $assertions();
    } finally {
      Console::$in= $in;
      Console::$out= $out;
      Console::$err= $err;
    }
  }

  #[Test]
  public function read() {
    $this->run(function() {
      Console::$in->redirect(new MemoryInputStream('.'));
      Assert::equals('.', Console::read());
    });
  }

  #[Test, Values(["Hello\nHallo", "Hello\rHallo", "Hello\r\nHallo",])]
  public function readLine($variation) {
    $this->run(function() use($variation) {
      Console::$in->redirect(new MemoryInputStream($variation));
      Assert::equals('Hello', Console::readLine());
      Assert::equals('Hallo', Console::readLine());
    });
  }

  #[Test]
  public function read_from_standard_input() {
    $this->run(function() {
      Console::$in->redirect(new MemoryInputStream('.'));
      Assert::equals('.', Console::$in->read(1));
    });
  }
 
  #[Test]
  public function readLine_from_standard_input() {
    $this->run(function() {
      Console::$in->redirect(new MemoryInputStream("Hello\nHallo\nOla"));
      Assert::equals('Hello', Console::$in->readLine());
      Assert::equals('Hallo', Console::$in->readLine());
      Assert::equals('Ola', Console::$in->readLine());
    });
  }
 
  #[Test]
  public function write() {
    $this->run(function() {
      Console::write('.');
      Assert::equals('.', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_multiple() {
    $this->run(function() {
      Console::write('.', 'o', 'O', '0');
      Assert::equals('.oO0', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_int() {
    $this->run(function() {
      Console::write(1);
      Assert::equals('1', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_true() {
    $this->run(function() {
      Console::write(true);
      Assert::equals('true', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_false() {
    $this->run(function() {
      Console::write(false);
      Assert::equals('false', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_float() {
    $this->run(function() {
      Console::write(1.5);
      Assert::equals('1.5', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_an_array() {
    $this->run(function() {
      Console::write([1, 2, 3]);
      Assert::equals('[1, 2, 3]', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_a_map() {
    $this->run(function() {
      Console::write(['key' => 'value', 'color' => 'blue']);
      Assert::equals(
        "[\n".
        "  key => \"value\"\n".
        "  color => \"blue\"\n".
        "]", 
        Console::$out->stream()->bytes()
      );
    });
  }

  #[Test]
  public function write_an_object() {
    $this->run(function() {
      Console::write(new class() implements Value {
        public function toString() { return 'Hello'; }
        public function hashCode() { return get_class($this); }
        public function compareTo($value) { return 1; }
      });
      Assert::equals('Hello', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function exception_from_toString() {
    $this->run(function() {
      try {
        Console::write(new class() implements Value {
          public function toString() { throw new IllegalStateException('Cannot render string'); }
          public function hashCode() { return get_class($this); }
          public function compareTo($value) { return 1; }
        });
        $this->fail('Expected exception not thrown', null, 'lang.IllegalStateException');
      } catch (IllegalStateException $expected) {
        Assert::equals('', Console::$out->stream()->bytes());
      }
    });
  }

  #[Test]
  public function write_to_standard_out() {
    $this->run(function() {
      Console::$out->write('.');
      Assert::equals('.', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function write_to_standard_error() {
    $this->run(function() {
      Console::$err->write('.');
      Assert::equals('.', Console::$err->stream()->bytes());
    });
  }

  #[Test]
  public function writef() {
    $this->run(function() {
      Console::writef('Hello "%s"', 'Timm');
      Assert::equals('Hello "Timm"', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function writef_to_standard_out() {
    $this->run(function() {
      Console::$out->writef('Hello "%s"', 'Timm');
      Assert::equals('Hello "Timm"', Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function writef_to_standard_error() {
    $this->run(function() {
      Console::$err->writef('Hello "%s"', 'Timm');
      Assert::equals('Hello "Timm"', Console::$err->stream()->bytes());
    });
  }

  #[Test]
  public function writeLine() {
    $this->run(function() {
      Console::writeLine('.');
      Assert::equals(".\n", Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function writeLine_to_standard_out() {
    $this->run(function() {
      Console::$out->writeLine('.');
      Assert::equals(".\n", Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function writeLine_to_standard_error() {
    $this->run(function() {
      Console::$err->writeLine('.');
      Assert::equals(".\n", Console::$err->stream()->bytes());
    });
  }

  #[Test]
  public function writeLinef() {
    $this->run(function() {
      Console::writeLinef('Hello %s', 'World');
      Assert::equals("Hello World\n", Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function writeLinef_to_standard_out() {
    $this->run(function() {
      Console::$out->writeLinef('Hello %s', 'World');
      Assert::equals("Hello World\n", Console::$out->stream()->bytes());
    });
  }

  #[Test]
  public function writeLinef_to_standard_error() {
    $this->run(function() {
      Console::$err->writeLinef('Hello %s', 'World');
      Assert::equals("Hello World\n", Console::$err->stream()->bytes());
    });
  }

  #[Test]
  public function initialize_on_console() {
    $this->initialize(true, function() {
      Console::initialize(true);
      Assert::instance(ConsoleInputStream::class, Console::$in->stream());
      Assert::instance(ConsoleOutputStream::class, Console::$out->stream());
      Assert::instance(ConsoleOutputStream::class, Console::$err->stream());
    });
  }

  #[Test]
  public function initialize_without_console() {
    $this->initialize(false, function() {
      Assert::null(Console::$in->stream());
      Assert::null(Console::$out->stream());
      Assert::null(Console::$err->stream());
    });
  }
}