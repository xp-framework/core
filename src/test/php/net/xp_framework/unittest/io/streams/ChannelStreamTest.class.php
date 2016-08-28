<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\ChannelOutputStream;
use io\streams\ChannelInputStream;
use io\IOException;
use lang\Runnable;

/**
 * TestCase
 *
 * @see      xp://io.streams.ChannelOutputStream
 * @see      xp://io.streams.ChannelInputStream
 */
class ChannelStreamTest extends \unittest\TestCase {

  #[@test, @expect(IOException::class)]
  public function invalidOutputChannelName() {
    new ChannelOutputStream('@@invalid@@');
  }

  #[@test, @expect(IOException::class)]
  public function invalidInputChannelName() {
    new ChannelInputStream('@@invalid@@');
  }
  
  #[@test, @expect(IOException::class)]
  public function stdinIsNotAnOutputStream() {
    new ChannelOutputStream('stdin');
  }

  #[@test, @expect(IOException::class)]
  public function inputIsNotAnOutputStream() {
    new ChannelOutputStream('input');
  }

  #[@test, @expect(IOException::class)]
  public function stdoutIsNotAnInputStream() {
    new ChannelInputStream('stdout');
  }

  #[@test, @expect(IOException::class)]
  public function stderrIsNotAnInputStream() {
    new ChannelInputStream('stderr');
  }

  #[@test, @expect(IOException::class)]
  public function outputIsNotAnInputStream() {
    new ChannelInputStream('outpit');
  }

  #[@test, @expect(IOException::class)]
  public function writeToClosedChannel() {
    ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream("output");
        $s->close();
        $s->write("whatever");
      }
    });
  }

  #[@test, @expect(IOException::class)]
  public function readingFromClosedChannel() {
    ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelInputStream('input');
        $s->close();
        $s->read();
      }
    });
  }

  #[@test]
  public function output() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream('output');
        $s->write("+OK Hello");
      }
    });
    $this->assertEquals('+OK Hello', $r['output']);
  }

  #[@test]
  public function stdout() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream('stdout');
        $s->write("+OK Hello");
      }
    });
    $this->assertEquals('+OK Hello', $r['stdout']);
  }

  #[@test]
  public function stderr() {
    $r= ChannelWrapper::capture(new class() implements Runnable {
      public function run() {
        $s= new ChannelOutputStream('stderr');
        $s->write("+OK Hello");
      }
    });
    $this->assertEquals('+OK Hello', $r['stderr']);
  }

  #[@test]
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

  #[@test]
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
}
