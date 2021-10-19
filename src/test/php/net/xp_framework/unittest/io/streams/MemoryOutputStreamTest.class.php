<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\MemoryOutputStream;
use unittest\{Test, Values};

/**
 * Unit tests for streams API
 *
 * @see   xp://io.streams.OutputStream
 * @see   xp://lang.Closeable#close
 */
class MemoryOutputStreamTest extends \unittest\TestCase {

  #[Test]
  public function can_create() {
    new MemoryOutputStream();
  }

  #[Test]
  public function initially_empty() {
    $this->assertEquals('', (new MemoryOutputStream())->bytes());
  }

  #[Test]
  public function initial_value() {
    $this->assertEquals('Test', (new MemoryOutputStream('Test'))->bytes());
  }

  #[Test]
  public function writing_a_string() {
    $out= new MemoryOutputStream();
    $out->write('Hello');
    $this->assertEquals('Hello', $out->bytes());
  }

  #[Test]
  public function writing_a_number() {
    $out= new MemoryOutputStream();
    $out->write(5);
    $this->assertEquals('5', $out->bytes());
  }

  #[Test]
  public function tell() {
    $out= new MemoryOutputStream('Hello');
    $this->assertEquals(5, $out->tell());
  }

  #[Test]
  public function tell_after_writing() {
    $out= new MemoryOutputStream();
    $out->write('Hello');
    $this->assertEquals(5, $out->tell());
  }

  #[Test, Values([0, 1, 5])]
  public function tell_after_seeking_to_beginning_plus($offset) {
    $out= new MemoryOutputStream('Hello');
    $out->seek($offset, SEEK_SET);
    $this->assertEquals($offset, $out->tell());
  }

  #[Test, Values([0, 1, 5])]
  public function tell_after_seeking_to_end_minus($offset) {
    $out= new MemoryOutputStream('Hello');
    $out->seek(-$offset, SEEK_END);
    $this->assertEquals(5 - $offset, $out->tell());
  }

  #[Test]
  public function replacing_character() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(0, SEEK_SET);
    $out->write('h');
    $this->assertEquals('hello', $out->bytes());
  }

  #[Test]
  public function seeking_to_end() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(0, SEEK_END);
    $out->write('!');
    $this->assertEquals('Hello!', $out->bytes());
  }

  #[Test]
  public function seeking_to_end_minus_one() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(-1, SEEK_END);
    $out->write('_');
    $this->assertEquals('Hell_', $out->bytes());
  }

  #[Test]
  public function overwriting() {
    $out= new MemoryOutputStream();
    $out->write('Hello');
    $out->seek(1, SEEK_SET);
    $out->write('ai');
    $out->write('fisch');
    $this->assertEquals('Haifisch', $out->bytes());
  }

  #[Test]
  public function closing_twice_has_no_effect() {
    $out= new MemoryOutputStream();
    $out->close();
    $out->close();
  }

  #[Test]
  public function truncate_to_same_length() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(5);
    $this->assertEquals('Hello', $out->bytes());
  }

  #[Test]
  public function truncate_to_zero() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(0);
    $this->assertEquals('', $out->bytes());
  }

  #[Test]
  public function shorten_using_truncate() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(4);
    $this->assertEquals('Hell', $out->bytes());
  }

  #[Test]
  public function lengthen_using_truncate() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(6);
    $this->assertEquals("Hello\x00", $out->bytes());
  }

  #[Test]
  public function truncate_does_not_change_file_offset() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(0, SEEK_SET);
    $out->truncate(5);
    $out->write('Ha');
    $this->assertEquals('Hallo', $out->bytes());
  }

  #[Test]
  public function writing_beyond_stream_end_padds_with_zero() {
    $out= new MemoryOutputStream();
    $out->seek(2, SEEK_SET);
    $out->write('Test');
    $out->write('!');

    $this->assertEquals("\x00\x00Test!", $out->bytes());
  }
}