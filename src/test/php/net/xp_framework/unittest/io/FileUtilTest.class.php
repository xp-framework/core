<?php namespace net\xp_framework\unittest\io;

use unittest\TestCase;
use io\FileUtil;
use io\File;
use io\streams\Streams;
use io\streams\MemoryInputStream;
use io\streams\MemoryOutputStream;

/**
 * TestCase
 *
 * @see   xp://io.FileUtil
 * @see   https://github.com/xp-framework/xp-framework/pull/220
 */
class FileUtilTest extends TestCase {

  #[@test]
  public function get_contents() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    $this->assertEquals('Test', FileUtil::getContents($f));
  }

  #[@test]
  public function set_contents_returns_number_of_written_bytes() {
    $f= new File(Streams::writeableFd(new MemoryOutputStream()));
    $this->assertEquals(4, FileUtil::setContents($f, 'Test'));
  }

  #[@test]
  public function contents_roundtrip() {
    $data= 'Test';
    $f= new Buffer();
    FileUtil::setContents($f, $data);
    $this->assertEquals($data, FileUtil::getContents($f));
  }

  #[@test]
  public function get_contents_read_returns_less_than_size() {
    $f= new File(Streams::readableFd(newinstance('io.streams.MemoryInputStream', ['Test'], [
      'read' => function($size= 4096) { return parent::read(min(1, $size)); }
    ])));
    $this->assertEquals('Test', FileUtil::getContents($f));
  }
}
