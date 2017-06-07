<?php namespace net\xp_framework\unittest\text\encode;

use unittest\actions\VerifyThat;
use io\streams\MemoryOutputStream;
use text\encode\Base64OutputStream;
use net\xp_framework\unittest\IgnoredOnHHVM;

/**
 * Test base64 encoder
 *
 * @see   xp://text.encode.Base64OutputStream
 */
#[@action([
#  new VerifyThat(function() { return in_array("convert.*", stream_get_filters()); }),
#  new IgnoredOnHHVM('bz2 stream filter ignores compression level')
#])]
class Base64OutputStreamTest extends \unittest\TestCase {

  /**
   * Test single write
   *
   */
  #[@test]
  public function singleWrite() {
    $out= new MemoryOutputStream();
    $stream= new Base64OutputStream($out);
    $stream->write('Hello');
    $stream->close();
    $this->assertEquals(base64_encode('Hello'), $out->getBytes());
  }

  /**
   * Test single write
   *
   */
  #[@test]
  public function lineWrappedAt76Characters() {
    $data= str_repeat('1', 75).str_repeat('2', 75);
    $out= new MemoryOutputStream();
    $stream= new Base64OutputStream($out, 76);
    $stream->write($data);
    $stream->close();
    $this->assertEquals(rtrim(chunk_split(base64_encode($data), 76, "\n"), "\n"), $out->getBytes());
  }

  /**
   * Test multiple consecutive writes
   *
   */
  #[@test]
  public function multipeWrites() {
    $out= new MemoryOutputStream();
    $stream= new Base64OutputStream($out);
    $stream->write('Hello');
    $stream->write(' ');
    $stream->write('World');
    $stream->close();
    $this->assertEquals(base64_encode('Hello World'), $out->getBytes());
  }
}
