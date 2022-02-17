<?php namespace io\streams;

use lang\IllegalArgumentException;

/**
 * Brotli output stream
 *
 * @ext  brotli
 * @test net.xp_framework.unittest.io.streams.BrCompressingOutputStreamTest
 * @see  https://github.com/kjdev/php-ext-brotli
 */
class BrCompressingOutputStream implements OutputStream {
  private $out, $handle;

  /**
   * Creates a new compressing output stream
   *
   * @param  io.streams.OutputStream $out The stream to write to
   * @param  int $level
   * @throws lang.IllegalArgumentException
   */
  public function __construct(OutputStream $out, $level= BROTLI_COMPRESS_LEVEL_MAX) {
    if ($level < BROTLI_COMPRESS_LEVEL_MIN || $level > BROTLI_COMPRESS_LEVEL_MAX) {
      throw new IllegalArgumentException('Level must be between '.BROTLI_COMPRESS_LEVEL_MIN.' and '.BROTLI_COMPRESS_LEVEL_MAX);
    }

    $this->out= $out;
    $this->handle= brotli_compress_init($level);
  }

  /**
   * Write a string
   *
   * @param  var $arg
   * @return void
   */
  public function write($arg) {
    $this->out->write(brotli_compress_add($this->handle, $arg, BROTLI_PROCESS));
  }

  /**
   * Flush this buffer
   *
   * @return void
   */
  public function flush() {
    $this->out->flush();
  }

  /**
   * Closes this object. May be called more than once, which may
   * not fail - that is, if the object is already closed, this 
   * method should have no effect.
   *
   * @throws lang.XPException
   */
  public function close() {
    if ($this->handle) {
      $this->out->write(brotli_compress_add($this->handle, '', BROTLI_FINISH));
      $this->handle= null;
    }
    $this->out->close();
  }
}