<?php namespace io\unittest;

use io\Channel;
use io\streams\{MemoryInputStream, MemoryOutputStream, TextWriter};
use lang\IllegalArgumentException;
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Expect, Test};

class TextWriterTest {
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
    Assert::equals('Hello', $this->out->bytes());
  }

  #[Test]
  public function writeOne() {
    $this->newWriter()->write('H');
    Assert::equals('H', $this->out->bytes());
  }

  #[Test]
  public function writeEmpty() {
    $this->newWriter()->write('');
    Assert::equals('', $this->out->bytes());
  }

  #[Test]
  public function writeLine() {
    $this->newWriter()->writeLine('Hello');
    Assert::equals("Hello\n", $this->out->bytes());
  }

  #[Test]
  public function writeEmptyLine() {
    $this->newWriter()->writeLine();
    Assert::equals("\n", $this->out->bytes());
  }

  #[Test]
  public function unixLineSeparatorIsDefault() {
    Assert::equals("\n", $this->newWriter()->newLine());
  }

  #[Test]
  public function withNewLine() {
    $w= $this->newWriter()->withNewLine("\r");
    Assert::equals("\r", $w->newLine());
  }

  #[Test]
  public function writeLineWindows() {
    $this->newWriter()->withNewLine("\r\n")->writeLine();
    Assert::equals("\r\n", $this->out->bytes());
  }

  #[Test]
  public function writeLineUnix() {
    $this->newWriter()->withNewLine("\n")->writeLine();
    Assert::equals("\n", $this->out->bytes());
  }

  #[Test]
  public function writeLineMac() {
    $this->newWriter()->withNewLine("\r")->writeLine();
    Assert::equals("\r", $this->out->bytes());
  }

  #[Test]
  public function writeUtf8() {
    $this->newWriter('utf-8')->write('Übercoder');
    Assert::equals("\303\234bercoder", $this->out->bytes());
  }

  #[Test]
  public function writeLineUtf8() {
    $this->newWriter('utf-8')->writeLine('Übercoder');
    Assert::equals("\303\234bercoder\n", $this->out->bytes());
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
    Assert::equals('Hello', $this->out->bytes());
  }
 
  #[Test]
  public function utf8Bom() {
    $this->newWriter('utf-8')->withBom()->write('Hello');
    Assert::equals("\357\273\277Hello", $this->out->bytes());
  }

  #[Test]
  public function utf16beBom() {
    $this->newWriter('utf-16be')->withBom()->write('Hello');
    Assert::equals("\376\377\0H\0e\0l\0l\0o", $this->out->bytes());
  }

  #[Test]
  public function utf16leBom() {
    $this->newWriter('utf-16le')->withBom()->write('Hello');
    Assert::equals("\377\376H\0e\0l\0l\0o\0", $this->out->bytes());
  }
}