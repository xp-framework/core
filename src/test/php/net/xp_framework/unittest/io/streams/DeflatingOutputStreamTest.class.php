<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\DeflatingOutputStream;
use unittest\Assert;

/**
 * TestCase
 *
 * @ext      zlib
 * @see      xp://io.streams.DeflatingOutputStream
 */
class DeflatingOutputStreamTest extends AbstractCompressingOutputStreamTest {

  /** @return string */
  protected function filter() { return 'zlib.*'; }

  /**
   * Get stream
   *
   * @param   io.streams.OutputStream wrapped
   * @return  int level
   * @return  io.streams.OutputStream
   */
  protected function newStream(\io\streams\OutputStream $wrapped, $level) {
    return new DeflatingOutputStream($wrapped, $level);
  }

  /**
   * Compress data
   *
   * @param   string in
   * @return  int level
   * @return  string
   */
  protected function compress($in, $level) {
    return gzdeflate($in, $level);
  }
}