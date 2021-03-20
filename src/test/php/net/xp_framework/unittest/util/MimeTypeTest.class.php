<?php namespace net\xp_framework\unittest\util;
 
use unittest\{Test, Values, TestCase};
use util\MimeType;

class MimeTypeTest extends TestCase {
  
  #[Test]
  public function text_file() {
    $this->assertEquals('text/plain', MimeType::getByFilename('test.txt'));
  }

  #[Test]
  public function html_file() {
    $this->assertEquals('text/html', MimeType::getByFilename('test.html'));
  }

  #[Test]
  public function gz_file() {
    $this->assertEquals('application/gzip', MimeType::getByFilename('test.gz'));
  }

  #[Test]
  public function uppercase_extension() {
    $this->assertEquals('text/html', MimeType::getByFilename('test.HTML'));
  }

  /** @see https://github.com/xp-framework/core/issues/264 */
  #[Test]
  public function favicon_file() {
    $this->assertEquals('image/x-icon', MimeType::getByFilename('favicon.ico'));
  }

  /** @see https://superuser.com/questions/901962/what-is-the-correct-mime-type-for-a-tar-gz-file */
  #[Test]
  public function double_extension() {
    $this->assertEquals('application/gzip', MimeType::getByFilename('test.tar.gz'));
  }

  #[Test, Values(['test', 'test.unknown', 'test.', '.', '..', '.htaccess'])]
  public function unknown_extension($name) {
    $this->assertEquals('application/octet-stream', MimeType::getByFilename($name));
  }

  #[Test, Values(['test', 'test.unknown', 'test.', '.', '..', '.htaccess'])]
  public function supplied_default_value($name) {
    $mime= 'application/php-serialized';
    $this->assertEquals($mime, MimeType::getByFilename($name, $mime));
  }
}