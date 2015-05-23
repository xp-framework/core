<?php namespace io\streams;

use lang\IllegalArgumentException;

/**
 * Represents the lines inside an input stream of bytes, delimited
 * by either Unix, Mac or Windows line endings.
 *
 * @see   xp://io.streams.TextReader#lines
 * @test  xp://net.xp_framework.unittest.io.streams.LinesTest
 */
class LinesIn extends \lang\Object implements \Iterator {
  const EOF = -1;
  private $reader, $line, $number;

  /**
   * Creates a new lines instance
   *
   * @param  var $arg Either a string, a TextReader or an InputStream
   * @param  string $charset Only taken into account when passing strings or streams
   * @throws lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= \xp::ENCODING) {
    if ($arg instanceof TextReader) {
      $this->reader= $arg;
    } else if ($arg instanceof InputStream) {
      $this->reader= new TextReader($arg, $charset);
    } else if (is_string($arg)) {
      $this->reader= new TextReader(new MemoryInputStream($arg), $charset);
    } else {
      throw new IllegalArgumentException('Given argument is neither a TextReader nor an InputStream: '.\xp::typeOf($arg));
    }
  }

  /** @return string */
  public function current() { return $this->line; }

  /** @return int */
  public function key() { return $this->number; }

  /** @return bool */
  public function valid() { return self::EOF !== $this->number; }

  /** @return void */
  public function next() {
    if (self::EOF === $this->number) {
      // Already at EOF, don't attempt further reads
    } else if (null === ($this->line= $this->reader->readLine())) {
      $this->number= self::EOF;
    } else {
      $this->number++;
    }
  }

  /** @return void */
  public function rewind() {
    $this->reader->atBeginning() || $this->reader->reset();
    $this->number= 0;
    $this->next();
  }
}