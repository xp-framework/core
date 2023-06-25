<?php namespace io\unittest;

use io\streams\Bz2CompressingOutputStream;
use test\verify\Condition;

#[Condition(assert: 'in_array("bzip2.*", stream_get_filters())')]
class Bz2CompressingOutputStreamTest extends AbstractCompressingOutputStreamTest {

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