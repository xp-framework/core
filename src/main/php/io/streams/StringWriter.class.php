<?php namespace io\streams;

/**
 * A OutputStreamWriter implementation that writes the string values of
 * the given arguments to the underlying output stream.
 *
 * @test  xp://net.xp_framework.unittest.io.streams.StringWriterTest
 */
class StringWriter implements OutputStreamWriter {
  protected $out= null;
  
  /**
   * Constructor
   *
   * @param   io.streams.OutputStream out
   */
  public function __construct($out) {
    $this->out= $out;
  }
  
  /**
   * Return underlying output stream
   *
   * @return  io.streams.OutputStream
   */
  public function getStream() {
    return $this->out;
  }

  /**
   * Return underlying output stream
   *
   * @param   io.streams.OutputStream stream
   */
  public function setStream(OutputStream $stream) {
    $this->out= $stream;
  }

  /**
   * Creates a string representation of this writer
   *
   * @return  string
   */
  public function toString() {
    return nameof($this)."@{\n  ".$this->out->toString()."\n}";
  }

  /**
   * Flush output buffer
   *
   */
  public function flush() {
    $this->out->flush();
  }

  /**
   * Print arguments
   *
   * @param   var... args
   */
  public function write(... $args) {
    foreach ($args as $arg) {
      if (is_string($arg)) {
        $this->out->write($arg);
      } else {
        $this->out->write(\xp::stringOf($arg));
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
        $this->out->write($arg);
      } else {
        $this->out->write(\xp::stringOf($arg));
      }
    }
    $this->out->write("\n");
  }
  
  /**
   * Print a formatted string
   *
   * @param   string format
   * @param   var... args
   * @see     php://writef
   */
  public function writef($format, ... $args) {
    $this->out->write(vsprintf($format, $args));
  }

  /**
   * Print a formatted string and append a newline
   *
   * @param   string format
   * @param   var... args
   */
  public function writeLinef($format, ... $args) {
    $this->out->write(vsprintf($format, $args)."\n");
  }
}
