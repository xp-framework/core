<?php namespace net\xp_framework\unittest\util;
 
use unittest\{Test, TestCase};
use util\MimeType;


/**
 * Test MimeType class
 *
 * @see  xp://util.MimeType
 */
class MimeTypeTest extends TestCase {
  
  /**
   * Tests getByFilename()
   */
  #[Test]
  public function text_file() {
    $this->assertEquals('text/plain', MimeType::getByFilename('test.txt'));
  }

  /**
   * Tests getByFilename()
   */
  #[Test]
  public function html_file() {
    $this->assertEquals('text/html', MimeType::getByFilename('test.html'));
  }

  /**
   * Tests getByFilename()
   */
  #[Test]
  public function uppercase_extension() {
    $this->assertEquals('text/html', MimeType::getByFilename('test.HTML'));
  }

  /**
   * Tests getByFilename()
   */
  #[Test]
  public function single_extension() {
    $this->assertEquals('application/x-gunzip', MimeType::getByFilename('test.gz'));
  }

  /**
   * Tests getByFilename()
   */
  #[Test]
  public function double_extension() {
    $this->assertEquals('application/x-tar-gz', MimeType::getByFilename('test.tar.gz'));
  }
}