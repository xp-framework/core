<?php namespace io\unittest;

use io\streams\{GzDecompressingInputStream, InputStream, MemoryInputStream};
use test\verify\Condition;
use test\{Assert, Test, Values};

#[Condition(assert: 'in_array("zlib.*", stream_get_filters())')]
class GzDecompressingInputStreamTest extends AbstractDecompressingInputStreamTest {

  /**
   * Get stream
   *
   * @param   io.streams.InputStream $wrapped
   * @return  int level
   * @return  io.streams.InputStream
   */
  protected function newStream(InputStream $wrapped) {
    return new GzDecompressingInputStream($wrapped);
  }

  /**
   * Compress data
   *
   * @param   string $in
   * @return  int $level
   * @return  string
   */
  protected function compress($in, $level) {
    return gzencode($in, $level);
  }

  /** @return var[][] */
  private function dataWithFileName() {
    return ["\x1F\x8B\x08\x08\x82\x86\xE0T\x00\x03test.txt\x00\xF3H\xCD\xC9\xC9\x07\x00\x82\x89\xD1\xF7\x05\x00\x00\x00"];
  }

  #[Test, Values(from: 'dataWithFileName')]
  public function data_with_original_filename($data) {
    $decompressor= $this->newStream(new MemoryInputStream($data));
    $chunk= $decompressor->read();
    $decompressor->close();
    Assert::equals('Hello', $chunk);
  }

  #[Test, Values(from: 'dataWithFileName')]
  public function header_with_original_filename($data) {
    $decompressor= $this->newStream(new MemoryInputStream($data));
    $decompressor->close();
    Assert::equals('test.txt', $decompressor->header()['filename']);
  }
}