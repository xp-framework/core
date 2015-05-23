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
   * @param  var $arg Either TextReader, a channel, a string or an input stream
   * @param  string $charset Not taken into account when created by a TextReader
   * @throws lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= \xp::ENCODING) {
    if ($arg instanceof TextReader) {
      $this->reader= $arg;
    } else {
      $this->reader= new TextReader($arg, $charset);
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