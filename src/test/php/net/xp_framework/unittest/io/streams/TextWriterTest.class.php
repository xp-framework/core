<?php namespace net\xp_framework\unittest\io\streams;

use io\Channel;
use io\streams\{MemoryInputStream, MemoryOutputStream, TextWriter};
use lang\IllegalArgumentException;
use unittest\actions\RuntimeVersion;
use unittest\{Expect, Test};

/**
 * TestCase
 *
 * @see      xp://io.streams.TextWriter
 */
class TextWriterTest extends \unittest\TestCase {
  protected $out= null;

  #[Test]
  public function can_create_with_stream() {
    new TextWriter(new MemoryOutputStream());
  }

  #[Test]
  public function can_create_with_channel() {
    new TextWriter(new class() implements Channel {
      public function in() { return new MemoryInputStream(''); }
      public function out() { return new MemoryOutputStream(); }
    });
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function raises_exception_for_incorrect_constructor_argument() {
    new TextWriter(null);
  }

  /**
   * Returns a text writer for a given output string.
   *
   * @param   string charset
   * @return  io.streams.TextWriter
   */
  protected function newWriter($charset= null) {
    $this->out= new MemoryOutputStream();
    return new TextWriter($this->out, $charset);
  }
  
  #[Test]
  public function write() {
    $this->newWriter()->write('Hello');
    $this->assertEquals('Hello', $this->out->bytes());
  }

  #[Test]
  public function writeOne() {
    $this->newWriter()->write('H');
    $this->assertEquals('H', $this->out->bytes());
  }

  #[Test]
  public function writeEmpty() {
    $this->newWriter()->write('');
    $this->assertEquals('', $this->out->bytes());
  }

  #[Test]
  public function writeLine() {
    $this->newWriter()->writeLine('Hello');
    $this->assertEquals("Hello\n", $this->out->bytes());
  }

  #[Test]
  public function writeEmptyLine() {
    $this->newWriter()->writeLine();
    $this->assertEquals("\n", $this->out->bytes());
  }

  #[Test]
  public function unixLineSeparatorIsDefault() {
    $this->assertEquals("\n", $this->newWriter()->getNewLine());
  }

  #[Test]
  public function setNewLine() {
    $w= $this->newWriter();
    $w->setNewLine("\r");
    $this->assertEquals("\r", $w->getNewLine());
  }

  #[Test]
  public function withNewLine() {
    $w= $this->newWriter()->withNewLine("\r");
    $this->assertEquals("\r", $w->getNewLine());
  }

  #[Test]
  public function writeLineWindows() {
    $this->newWriter()->withNewLine("\r\n")->writeLine();
    $this->assertEquals("\r\n", $this->out->bytes());
  }

  #[Test]
  public function writeLineUnix() {
    $this->newWriter()->withNewLine("\n")->writeLine();
    $this->assertEquals("\n", $this->out->bytes());
  }

  #[Test]
  public function writeLineMac() {
    $this->newWriter()->withNewLine("\r")->writeLine();
    $this->assertEquals("\r", $this->out->bytes());
  }

  #[Test]
  public function writeUtf8() {
    $this->newWriter('utf-8')->write('Übercoder');
    $this->assertEquals("\303\234bercoder", $this->out->bytes());
  }

  #[Test]
  public function writeLineUtf8() {
    $this->newWriter('utf-8')->writeLine('Übercoder');
    $this->assertEquals("\303\234bercoder\n", $this->out->bytes());
  }

  #[Test]
  public function closingTwice() {
    $w= $this->newWriter('');
    $w->close();
    $w->close();
  }

  #[Test]
  public function isoHasNoBom() {
    $this->newWriter('iso-8859-1')->withBom()->write('Hello');
    $this->assertEquals('Hello', $this->out->bytes());
  }
 
  #[Test]
  public function utf8Bom() {
    $this->newWriter('utf-8')->withBom()->write('Hello');
    $this->assertEquals("\357\273\277Hello", $this->out->bytes());
  }

  #[Test]
  public function utf16beBom() {
    $this->newWriter('utf-16be')->withBom()->write('Hello');
    $this->assertEquals("\376\377\0H\0e\0l\0l\0o", $this->out->bytes());
  }

  #[Test]
  public function utf16leBom() {
    $this->newWriter('utf-16le')->withBom()->write('Hello');
    $this->assertEquals("\377\376H\0e\0l\0l\0o\0", $this->out->bytes());
  }
}