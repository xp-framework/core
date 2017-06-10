<?php namespace io\streams;

use util\Objects;

/**
 * A OutputStreamWriter implementation that writes the string values of
 * the given arguments to the underlying output stream.
 *
 * @test  xp://net.xp_framework.unittest.io.streams.StringWriterTest
 */
class StringWriter extends Writer {

  /**
   * Return underlying output stream
   *
   * @deprecated Use redirect() instead
   * @param  io.streams.OutputStream stream
   * @return void
   */
  public function setStream(OutputStream $stream) {
    $this->redirect($stream);
  }

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