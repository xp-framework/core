<?php namespace net\xp_framework\unittest\io;

use io\streams\{Streams, MemoryInputStream, MemoryOutputStream};
use io\{File, FileUtil};
use unittest\TestCase;

/**
 * TestCase
 *
 * @see   xp://io.FileUtil
 * @see   https://github.com/xp-framework/xp-framework/pull/220
 */
class FileUtilTest extends TestCase {

  #[@test]
  public function read() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    $this->assertEquals('Test', FileUtil::read($f));
  }

  #[@test]
  public function write_returns_number_of_written_bytes() {
    $f= new File(Streams::writeableFd(new MemoryOutputStream()));
    $this->assertEquals(4, FileUtil::write($f, 'Test'));
  }

  #[@test]
  public function write_bytes() {
    $out= new MemoryOutputStream();
    FileUtil::write(new File(Streams::writeableFd($out)), 'Test');
    $this->assertEquals('Test', $out->bytes());
  }

  #[@test]
  public function read_returns_less_than_size() {
    $f= new File(Streams::readableFd(new class('Test') extends MemoryInputStream {
      public function read($size= 4096) { return parent::read(min(1, $size)); }
    }));
    $this->assertEquals('Test', FileUtil::read($f));
  }

  /** @deprecated */
  #[@test]
  public function get_contents() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    $this->assertEquals('Test', FileUtil::getContents($f));
  }

  /** @deprecated */
  #[@test]
  public function set_contents() {
    $f= new File(Streams::writeableFd(new MemoryOutputStream()));
    $this->assertEquals(4, FileUtil::setContents($f, 'Test'));
  }
}
