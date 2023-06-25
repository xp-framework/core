<?php namespace util\unittest;

use test\{Assert, Test, Values};
use util\MimeType;

class MimeTypeTest {
  
  #[Test]
  public function text_file() {
    Assert::equals('text/plain', MimeType::getByFilename('test.txt'));
  }

  #[Test]
  public function html_file() {
    Assert::equals('text/html', MimeType::getByFilename('test.html'));
  }

  #[Test]
  public function gz_file() {
    Assert::equals('application/gzip', MimeType::getByFilename('test.gz'));
  }

  #[Test]
  public function uppercase_extension() {
    Assert::equals('text/html', MimeType::getByFilename('test.HTML'));
  }

  /** @see https://github.com/xp-framework/core/issues/264 */
  #[Test]
  public function favicon_file() {
    Assert::equals('image/x-icon', MimeType::getByFilename('favicon.ico'));
  }

  /** @see https://superuser.com/questions/901962/what-is-the-correct-mime-type-for-a-tar-gz-file */
  #[Test]
  public function double_extension() {
    Assert::equals('application/gzip', MimeType::getByFilename('test.tar.gz'));
  }

  #[Test, Values(['test', 'test.unknown', 'test.', '.', '..', '.htaccess'])]
  public function unknown_extension($name) {
    Assert::equals('application/octet-stream', MimeType::getByFilename($name));
  }

  #[Test, Values(['test', 'test.unknown', 'test.', '.', '..', '.htaccess'])]
  public function supplied_default_value($name) {
    $mime= 'application/php-serialized';
    Assert::equals($mime, MimeType::getByFilename($name, $mime));
  }
}