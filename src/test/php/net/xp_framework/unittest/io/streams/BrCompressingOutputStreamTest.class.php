<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\{MemoryOutputStream, BrCompressingOutputStream};
use lang\IllegalArgumentException;
use unittest\{Assert, Before, Test, Values, PrerequisitesNotMetError};

class BrCompressingOutputStreamTest {

  #[Before]
  public function verify() {
    if (!extension_loaded('brotli')) {
      throw new PrerequisitesNotMetError('Brotli extension missing');
    }
  }

  #[Test]
  public function can_create() {
    new BrCompressingOutputStream(new MemoryOutputStream());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function using_invalid_compression_level() {
    new BrCompressingOutputStream(new MemoryOutputStream(), -1);
  }

  #[Test, Values([1, 6, 11])]
  public function write($level) {
    $out= new MemoryOutputStream();

    $fixture= new BrCompressingOutputStream($out, $level);
    $fixture->write('Hello');
    $fixture->write(' ');
    $fixture->write('World');
    $fixture->close();

    Assert::equals('Hello World', brotli_uncompress($out->bytes()));
  }
}