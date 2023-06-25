<?php namespace io\unittest;

use io\streams\Bz2DecompressingInputStream;
use test\verify\Condition;

#[Condition(assert: 'in_array("bzip2.*", stream_get_filters())')]
class Bz2DecompressingInputStreamTest extends AbstractDecompressingInputStreamTest {

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