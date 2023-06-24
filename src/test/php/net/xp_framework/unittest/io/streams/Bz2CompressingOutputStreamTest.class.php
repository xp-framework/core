<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\Bz2CompressingOutputStream;
use unittest\Assert;

/**
 * TestCase for BZIP2 compression
 *
 * @ext   bz2
 * @see   xp://io.streams.Bz2CompressingOutputStream
 */
class Bz2CompressingOutputStreamTest extends AbstractCompressingOutputStreamTest {

  /** @return string */
  protected function filter() { return 'bzip2.*'; }

  /**
   * Get stream
   *
   * @param   io.streams.OutputStream wrapped
   * @return  int level
   * @return  io.streams.OutputStream
   */
  protected function newStream(\io\streams\OutputStream $wrapped, $level) {
    return new Bz2CompressingOutputStream($wrapped, $level);
  }

  /**
   * Compress data
   *
   * @param   string in
   * @return  int level
   * @return  string
   */
  protected function compress($in, $level) {
    return bzcompress($in, $level);
  }
}