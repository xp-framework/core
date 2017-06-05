<?php namespace io\streams;

use lang\Closeable;

/**
 * Serves as an abstract base class for all other writers. A writer
 * writes characters to an underlying output stream implementation
 * (which works with bytes - for single-byte character sets, there is 
 * no difference, obviously).
 */
abstract class Writer implements Closeable {
  protected $stream= null;
  
  /**
   * Constructor. Creates a new Writer from an OutputStream.
   *
   * @param   io.streams.OutputStream stream
   */
  public function __construct(OutputStream $stream) {
    $this->stream= $stream;
  }
  
  /**
   * Returns the underlying stream
   *
   * @return  io.streams.OutputStream stream
   */
  public function getStream() {
    return $this->stream;
  }
  
  /**
   * Closes this reader (and the underlying stream)
   *
   */
  public function close() {
    $this->stream->close();
  }
}
