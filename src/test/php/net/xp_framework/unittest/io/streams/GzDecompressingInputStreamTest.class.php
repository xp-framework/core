<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\GzDecompressingInputStream;

/**
 * TestCase
 *
 * @ext   zlib
 * @see   xp://io.streams.GzDecompressingInputStream
 */
class GzDecompressingInputStreamTest extends AbstractDecompressingInputStreamTest {

  /** @return string */
  protected function filter() { return 'zlib.*'; }

  /**
   * Get stream
   *
   * @param   io.streams.InputStream wrapped
   * @return  int level
   * @return  io.streams.InputStream
   */
  protected function newStream(\io\streams\InputStream $wrapped) {
    return new GzDecompressingInputStream($wrapped);
  }

  /**
   * Compress data
   *
   * @param   string in
   * @return  int level
   * @return  string
   */
  protected function compress($in, $level) {
    return gzencode($in, $level);
  }
}
