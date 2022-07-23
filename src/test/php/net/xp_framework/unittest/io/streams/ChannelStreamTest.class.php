<?php namespace net\xp_framework\unittest\io\streams;

use io\IOException;
use io\streams\{ChannelInputStream, ChannelOutputStream};
use lang\Runnable;
use unittest\{Expect, Test};

/**
 * TestCase
 *
 * @see      xp://io.streams.ChannelOutputStream
 * @see      xp://io.streams.ChannelInputStream
 */
class ChannelStreamTest extends \unittest\TestCase {

  #[Test, Expect(IOException::class)]
  public function invalidOutputChannelName() {
    new ChannelOutputStream('@@invalid@@');
  }

  #[Test, Expect(IOException::class)]
  public function invalidInputChannelName() {
    new ChannelInputStream('@@invalid@@');
  }
  
  #[Test, Expect(IOException::class)]
  public function stdinIsNotAnOutputStream() {
    new ChannelOutputStream('stdin');
  }

  #[Test, Expect(IOException::class)]
  public function inputIsNotAnOutputStream() {
    new ChannelOutputStream('input');
  }

  #[Test, Expect(IOException::class)]
  public function stdoutIsNotAnInputStream() {
    new ChannelInputStream('stdout');
  }

  #[Test, Expect(IOException::class)]
  public function stderrIsNotAnInputStream() {
    new ChannelInputStream('stderr');
  }

  #[Test, Expect(IOException::class)]
  public function outputIsNotAnInputStream() {
    new ChannelInputStream('outpit');
  }

  #[Test, Expect(IOException::class)]
  public function writeToClosedChannel() {
    ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream("output");
        $s->close();
        $s->write("whatever");
      }
    });
  }

  #[Test, Expect(IOException::class)]
  public function readingFromClosedChannel() {
    ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelInputStream('input');
        $s->close();
        $s->read();
      }
    });
  }

  #[Test]
  public function output() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream('output');
        $s->write("+OK Hello");
      }
    });
    $this->assertEquals('+OK Hello', $r['output']);
  }

  #[Test]
  public function stdout() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream('stdout');
        $s->write("+OK Hello");
      }
    });
    $this->assertEquals('+OK Hello', $r['stdout']);
  }

  #[Test]
  public function stderr() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream('stderr');
        $s->write("+OK Hello");
      }
    });
    $this->assertEquals('+OK Hello', $r['stderr']);
  }

  #[Test]
  public function stdin() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $i= new ChannelInputStream('stdin');
        $o= new ChannelOutputStream('stdout');
        while ($i->available()) {
          $o->write($i->read());
        }
      }
    }, ['stdin' => '+OK Piped input']);
    $this->assertEquals('+OK Piped input', $r['stdout']);
  }

  #[Test]
  public function input() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $i= new ChannelInputStream('input');
        $o= new ChannelOutputStream('stdout');
        while ($i->available()) {
          $o->write($i->read());
        }
      }
    }, ['input' => '+OK Piped input']);      
    $this->assertEquals('+OK Piped input', $r['stdout']);
  }

  #[Test]
  public function input_name() {
    $s= new ChannelInputStream('input');
    $this->assertEquals('io.streams.ChannelInputStream(channel=input)', $s->toString());
  }

  #[Test]
  public function output_name() {
    $s= new ChannelOutputStream('output');
    $this->assertEquals('io.streams.ChannelOutputStream(channel=output)', $s->toString());
  }
}