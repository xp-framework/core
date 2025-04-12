<?php namespace io\streams;

use util\Objects;

/**
 * A OutputStreamWriter implementation that writes the string values of
 * the given arguments to the underlying output stream.
 *
 * @test  io.unittest.StringWriterTest
 */
class StringWriter extends Writer {

  /**
   * Writes text
   *
   * @param  string $text
   * @return int
   */
  protected function write0($text) {
    $this->stream->write($text);
  }
}