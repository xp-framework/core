<?php namespace io\unittest;

use io\IOException;
use io\streams\MemoryOutputStream;
use test\{Assert, Expect, Test, Values};

class MemoryOutputStreamTest {

  #[Test]
  public function can_create() {
    new MemoryOutputStream();
  }

  #[Test]
  public function initially_empty() {
    $out= new MemoryOutputStream();
    Assert::equals('', $out->bytes());
  }

  #[Test]
  public function initial_value() {
    $out= new MemoryOutputStream('Test');
    Assert::equals('Test', $out->bytes());
  }

  #[Test]
  public function writing_a_string() {
    $out= new MemoryOutputStream();
    $out->write('Test');
    Assert::equals('Test', $out->bytes());
  }

  #[Test]
  public function writing_a_number() {
    $out= new MemoryOutputStream();
    $out->write(5);
    Assert::equals('5', $out->bytes());
  }

  #[Test]
  public function tell() {
    $out= new MemoryOutputStream('Hello');
    Assert::equals(5, $out->tell());
  }

  #[Test]
  public function tell_after_writing() {
    $out= new MemoryOutputStream();
    $out->write('Hello');
    Assert::equals(5, $out->tell());
  }

  #[Test]
  public function size() {
    $out= new MemoryOutputStream();
    Assert::equals(0, $out->size());
  }

  #[Test]
  public function size_with_initial_value() {
    $out= new MemoryOutputStream('Hello');
    Assert::equals(5, $out->size());
  }

  #[Test]
  public function size_after_writing() {
    $out= new MemoryOutputStream();
    $out->write('Hello');
    Assert::equals(5, $out->size());
  }

  #[Test, Values([0, 1, 5])]
  public function tell_after_seeking_to_beginning_plus($offset) {
    $out= new MemoryOutputStream('Hello');
    $out->seek($offset, SEEK_SET);
    Assert::equals($offset, $out->tell());
  }

  #[Test, Values([0, 1, 5])]
  public function tell_after_seeking_to_end_minus($offset) {
    $out= new MemoryOutputStream('Hello');
    $out->seek(-$offset, SEEK_END);
    Assert::equals(5 - $offset, $out->tell());
  }

  #[Test]
  public function replacing_character() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(0, SEEK_SET);
    $out->write('h');
    Assert::equals('hello', $out->bytes());
  }

  #[Test]
  public function seeking_to_end() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(0, SEEK_END);
    $out->write('!');
    Assert::equals('Hello!', $out->bytes());
  }

  #[Test]
  public function seeking_to_end_minus_one() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(-1, SEEK_END);
    $out->write('_');
    Assert::equals('Hell_', $out->bytes());
  }

  #[Test, Expect(IOException::class)]
  public function seek_unknown_whence() {
    (new MemoryOutputStream(''))->seek(0, 9999);
  }

  #[Test, Expect(IOException::class)]
  public function seek_before_start() {
    (new MemoryOutputStream(''))->seek(-1);
  }

  #[Test]
  public function overwriting() {
    $out= new MemoryOutputStream();
    $out->write('Hello');
    $out->seek(1, SEEK_SET);
    $out->write('ai');
    $out->write('fisch');
    Assert::equals('Haifisch', $out->bytes());
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
    Assert::equals(5, $out->size());
    Assert::equals('Hello', $out->bytes());
  }

  #[Test]
  public function truncate_to_zero() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(0);
    Assert::equals(0, $out->size());
    Assert::equals('', $out->bytes());
  }

  #[Test]
  public function shorten_using_truncate() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(4);
    Assert::equals(4, $out->size());
    Assert::equals('Hell', $out->bytes());
  }

  #[Test]
  public function lengthen_using_truncate() {
    $out= new MemoryOutputStream('Hello');
    $out->truncate(6);
    Assert::equals(6, $out->size());
    Assert::equals("Hello\x00", $out->bytes());
  }

  #[Test]
  public function truncate_does_not_change_file_offset() {
    $out= new MemoryOutputStream('Hello');
    $out->seek(0, SEEK_SET);
    $out->truncate(5);
    $out->write('Ha');
    Assert::equals('Hallo', $out->bytes());
  }

  #[Test]
  public function writing_beyond_stream_end_padds_with_zero() {
    $out= new MemoryOutputStream();
    $out->seek(2, SEEK_SET);
    $out->write('Test');
    $out->write('!');

    Assert::equals("\x00\x00Test!", $out->bytes());
  }
}