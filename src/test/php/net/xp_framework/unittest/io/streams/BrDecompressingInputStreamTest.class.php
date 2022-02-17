<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\{BrDecompressingInputStream, MemoryInputStream};
use unittest\{Assert, Before, Test, Values, PrerequisitesNotMetError};

class BrDecompressingInputStreamTest {

  private function compressed() {
    foreach ([1, 6, 11] as $level) {
      yield [$level, ''];
      yield [$level, 'Test'];
      yield [$level, "GIF89a\x14\x12\x77..."];
    }
  }

  #[Before]
  public function verify() {
    if (!extension_loaded('brotli')) {
      throw new PrerequisitesNotMetError('Brotli extension missing');
    }
  }

  #[Test]
  public function can_create() {
    new BrDecompressingInputStream(new MemoryInputStream(''));
  }

  #[Test]
  public function read_plain() {
    $in= new BrDecompressingInputStream(new MemoryInputStream('Test'));
    $read= $in->read();
    $in->close();

    Assert::equals('', $read);
  }

  #[Test, Values('compressed')]
  public function read_compressed($level, $bytes) {
    $in= new BrDecompressingInputStream(new MemoryInputStream(brotli_compress($bytes, $level)));
    $read= $in->read();
    $rest= $in->available();
    $in->close();

    Assert::equals($bytes, $read);
    Assert::equals(0, $rest);
  }

  #[Test, Values([1, 8192, 16384])]
  public function read_all($length) {
    $bytes= random_bytes($length);
    $in= new BrDecompressingInputStream(new MemoryInputStream(brotli_compress($bytes)));

    $read= '';
    while ($in->available()) {
      $read.= $in->read();
    }
    $in->close();

    Assert::equals($bytes, $read);
  }
}