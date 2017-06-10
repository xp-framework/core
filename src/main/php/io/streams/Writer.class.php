<?php namespace io\streams;

use io\Channel;
use lang\{Closeable, Value, IllegalArgumentException};
use util\Objects;

/**
 * Serves as an abstract base class for all other writers. A writer
 * writes characters to an underlying output stream implementation
 * (which works with bytes - for single-byte character sets, there is 
 * no difference, obviously).
 */
abstract class Writer implements OutputStreamWriter, Closeable, Value {
  protected $stream= null;
  protected $newLine= "\n";
  
  /**
   * Constructor. Creates a new TextWriter on an underlying output
   * stream with a given charset.
   *
   * @param  io.streams.OutputStream|io.Channel|string $arg The target
   * @param  string $charset the character set to encode to.
   * @throws lang.IllegalArgumentException
   */
  public function __construct($arg, $charset= \xp::ENCODING) {
    if ($arg instanceof OutputStream) {
      $this->stream= $arg;
    } else if ($arg instanceof Channel) {
      $this->stream= $arg->out();
    } else if (is_string($arg)) {
      $this->stream= new MemoryOutputStream($arg);
    } else {
      throw new IllegalArgumentException('Given argument is neither an output stream, a channel nor a string: '.typeof($arg)->getName());
    }
  }

  /**
   * Returns the underlying stream
   *
   * @deprecated Use stream() instead
   * @return  io.streams.OutputStream stream
   */
  public function getStream() { return $this->stream; }

  /**
   * Returns the underlying stream
   *
   * @return  io.streams.OutputStream stream
   */
  public function stream() { return $this->stream; }

  /**
   * Return underlying output stream
   *
   * @param  io.streams.OutputStream stream
   * @return void
   */
  public function redirect(OutputStream $stream) {
    $this->stream= $stream;
  }

  /**
   * Gets newLine property's bytes
   *
   * @return  string newLine
   */
  public function newLine() { return $this->newLine; }

  /**
   * Sets newLine property's bytes and returns this writer
   *
   * @param  string $newLine
   * @return self
   */
  public function withNewLine($newLine) {
    $this->newLine= $newLine;
    return $this;
  }

  /**
   * Flush output buffer
   *
   * @return void
   */
  public function flush() {
    $this->stream->flush();
  }

  /**
   * Writes text. Implemented in subclasses.
   *
   * @param  string $text
   * @return int
   */
  protected abstract function write0($text);

  /**
   * Print arguments
   *
   * @param   var... args
   */
  public function write(... $args) {
    foreach ($args as $arg) {
      if (is_string($arg)) {
        $this->write0($arg);
      } else {
        $this->write0(Objects::stringOf($arg));
      }
    }
  }
  
  /**
   * Print arguments and append a newline
   *
   * @param   var... args
   */
  public function writeLine(... $args) {
    foreach ($args as $arg) {
      if (is_string($arg)) {
        $this->write0($arg);
      } else {
        $this->write0(Objects::stringOf($arg));
      }
    }
    $this->write0($this->newLine);
  }
  
  /**
   * Print a formatted string
   *
   * @param   string format
   * @param   var... args
   * @see     php://writef
   */
  public function writef($format, ... $args) {
    $this->write0(vsprintf($format, $args));
  }

  /**
   * Print a formatted string and append a newline
   *
   * @param   string format
   * @param   var... args
   */
  public function writeLinef($format, ... $args) {
    $this->write0(vsprintf($format, $args).$this->newLine);
  }

  /**
   * Closes this writer (and the underlying stream)
   *
   * @return void
   */
  public function close() {
    $this->stream->close();
  }

  /** @return void */
  public function __destruct() {
    $this->close();
  }

  /** @return string */
  public function toString() {
    return nameof($this)."@{\n  ".$this->stream->toString()."\n}";
  }

  /** @return string */
  public function hashCode() {
    return Objects::hashOf((array)$this);
  }

  /**
   * Compares this reader to a given value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->stream, $value->stream) : 1;
  }
}