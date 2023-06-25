<?php namespace io\unittest;

use io\streams\Bz2DecompressingInputStream;

class Bz2DecompressingInputStreamTest extends AbstractDecompressingInputStreamTest {

  /** @return string */
  protected function filter() { return 'bzip2.*'; }

  /**
   * Get stream
   *
   * @param   io.streams.InputStream wrapped
   * @return  int level
   * @return  io.streams.InputStream
   */
  protected function newStream(\io\streams\InputStream $wrapped) {
    return new Bz2DecompressingInputStream($wrapped);
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