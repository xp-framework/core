<?php namespace io\streams;

use Traversable, IteratorAggregate;
use lang\IllegalArgumentException;

/**
 * Represents the lines inside an input stream of bytes, delimited
 * by either Unix, Mac or Windows line endings.
 *
 * @see   io.streams.Reader#lines
 * @test  io.unittest.LinesInTest
 */
class LinesIn implements IteratorAggregate {
  private $reader, $reset;

  /**
   * Creates a new lines instance
   *
   * @param  io.streams.Reader|io.streams.InputStream|io.Channel|string $arg Input
   * @param  string $charset Not taken into account when created by a Reader
   * @param  bool $reset Whether to start from the beginning (default: true)
   * @throws lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= \xp::ENCODING, $reset= true) {
    if ($arg instanceof Reader) {
      $this->reader= $arg;
    } else {
      $this->reader= new TextReader($arg, $charset);
    }
    $this->reset= $reset;
  }

  /** Iterate over the lines */
  public function getIterator(): Traversable {
    if ($this->reset && !$this->reader->atBeginning()) {
      $this->reader->reset();
    }

    $number= 1;
    while (null !== ($line= $this->reader->readLine())) {
      yield $number++ => $line;
    }
  }
}