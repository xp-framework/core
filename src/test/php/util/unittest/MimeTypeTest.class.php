<?php namespace util\unittest;

use test\{Assert, Test, Values};
use util\MimeType;

class MimeTypeTest {

  /** @return iterable */
  private function tests() {
    yield ['test.txt', 'text/plain'];
    yield ['test.md', 'text/markdown'];
    yield ['test.xml', 'text/xml'];
    yield ['test.yaml', 'text/yaml'];
    yield ['test.csv', 'text/csv'];
    yield ['test.json', 'application/json'];

    yield ['test.htm', 'text/html'];
    yield ['test.html', 'text/html'];
    yield ['test.css', 'text/css'];
    yield ['test.js', 'text/javascript'];

    yield ['test.ttf', 'font/ttf'];
    yield ['test.otf', 'font/otf'];
    yield ['test.woff', 'font/woff'];
    yield ['test.woff2', 'font/woff2'];

    yield ['test.svg', 'image/svg+xml'];
    yield ['test.gif', 'image/gif'];
    yield ['test.png', 'image/png'];
    yield ['test.jpg', 'image/jpeg'];
    yield ['test.jpeg', 'image/jpeg'];
    yield ['test.avif', 'image/avif'];
    yield ['test.webp', 'image/webp'];

    yield ['test.mp3', 'audio/mp3'];
    yield ['test.wav', 'audio/wav'];
    yield ['test.aac', 'audio/aac'];

    yield ['test.mp4', 'video/mp4'];
    yield ['test.webm', 'video/webm'];
    yield ['test.mov', 'video/quicktime'];
  }

  #[Test, Values(from: 'tests')]
  public function wellknown($filename, $expected) {
    Assert::equals($expected, MimeType::getByFilename($filename));
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