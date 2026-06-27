<?php namespace io\unittest;

use ArrayObject;
use io\streams\{MemoryInputStream, InputStream};
use io\{Blob, NotSupported};
use lang\IllegalArgumentException;
use test\{Assert, Expect, Test, Values};
use util\Bytes;

class BlobTest {

  /** @return iterable */
  private function cases() {
    yield [new Blob(), []];
    yield [new Blob('Test'), ['Test']];
    yield [new Blob(['Über']), ['Über']];
    yield [new Blob([new Blob(['Test']), 'ed']), ['Test', 'ed']];
    yield [new Blob(['Test', 'ed']), ['Test', 'ed']];
    yield [new Blob((function() { yield 'Test'; yield 'ed'; })()), ['Test', 'ed']];
    yield [new Blob(new ArrayObject(['Test', 'ed'])), ['Test', 'ed']];
    yield [new Blob(new Bytes('Test')), ['Test']];
    yield [new Blob(new MemoryInputStream('Test')), ['Test']];
  }

  #[Test]
  public function can_create() {
    new Blob();
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function not_from_null() {
    new Blob(null);
  }

  #[Test]
  public function meta_empty_by_default() {
    Assert::equals([], (new Blob('Test'))->meta);
  }

  #[Test]
  public function meta() {
    $meta= ['type' => 'text/plain'];
    Assert::equals($meta, (new Blob('Test', $meta))->meta);
  }

  #[Test, Values(from: 'cases')]
  public function iteration($fixture, $expected) {
    Assert::equals($expected, iterator_to_array($fixture));
  }

  #[Test, Values(from: 'cases')]
  public function bytes($fixture, $expected) {
    Assert::equals(new Bytes($expected), $fixture->bytes());
  }

  #[Test, Values(from: 'cases')]
  public function stream($fixture, $expected) {
    $stream= $fixture->stream();
    $data= [];
    while ($stream->available()) {
      $data[]= $stream->read();
    }
    Assert::equals($expected, $data);
  }

  #[Test, Values(from: 'cases')]
  public function string_cast($fixture, $expected) {
    Assert::equals(implode('', $expected), (string)$fixture);
  }

  #[Test, Values([[1, ['T', 'e', 's', 't']], [2, ['Te', 'st']], [3, ['Tes', 't']], [4, ['Test']]])]
  public function slices($size, $expected) {
    Assert::equals($expected, iterator_to_array((new Blob('Test'))->slices($size)));
  }

  #[Test]
  public function fill_slice() {
    Assert::equals(['Test'], iterator_to_array((new Blob(['Te', 'st']))->slices()));
  }

  #[Test]
  public function fetch_slice_twice() {
    $fixture= new Blob('Test');

    Assert::equals(['Test'], iterator_to_array($fixture->slices()));
    Assert::equals(['Test'], iterator_to_array($fixture->slices()));
  }

  #[Test]
  public function cannot_fetch_slices_twice_from_non_seekable() {
    $fixture= new Blob(new class() implements InputStream {
      private $input= ['Test'];
      public function available() { return strlen(current($this->input)); }
      public function read($limit= 8192) { return array_shift($this->input); }
      public function close() { $this->input= []; }
    });
    iterator_to_array($fixture->slices());

    Assert::throws(NotSupported::class, fn() => iterator_to_array($fixture->slices()));
  }

  /** @see https://bugs.php.net/bug.php?id=77069 */
  #[Test]
  public function base64_encoded() {
    $base64= (new Blob('Test'))->encoded('convert.base64-encode');

    Assert::equals(['convert.base64-encode'], $base64->meta['encoding']);
    Assert::equals('VGVzdA==', (string)$base64);
  }

  #[Test]
  public function custom_encoding() {
    $base64= (new Blob('Test'))->encoded('uppercase', fn($chunk) => strtoupper($chunk));

    Assert::equals(['uppercase'], $base64->meta['encoding']);
    Assert::equals('TEST', (string)$base64);
  }
}