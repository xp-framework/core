<?php namespace net\xp_framework\unittest\io\streams;

use io\streams\MemoryOutputStream;

/**
 * Unit tests for streams API
 *
 * @see   xp://io.streams.OutputStream
 * @see   xp://lang.Closeable#close
 */
class MemoryOutputStreamTest extends \unittest\TestCase {
  private $out;

  /**
   * Setup method. Creates the fixture.
   */
  public function setUp() {
    $this->out= new MemoryOutputStream();
  }

  #[@test]
  public function initially_empty() {
    $this->assertEquals('', $this->out->bytes());
  }

  #[@test]
  public function writing_a_string() {
    $this->out->write('Hello');
    $this->assertEquals('Hello', $this->out->bytes());
  }

  #[@test]
  public function writing_a_number() {
    $this->out->write(5);
    $this->assertEquals('5', $this->out->bytes());
  }

  #[@test]
  public function tell() {
    $this->out->write('Hello');
    $this->assertEquals(5, $this->out->tell());
  }

  #[@test, @values([0, 1, 5])]
  public function tell_after_seeking_to_beginning_plus($offset) {
    $this->out->write('Hello');
    $this->out->seek($offset, SEEK_SET);
    $this->assertEquals($offset, $this->out->tell());
  }

  #[@test, @values([0, 1, 5])]
  public function tell_after_seeking_to_end_minus($offset) {
    $this->out->write('Hello');
    $this->out->seek(-$offset, SEEK_END);
    $this->assertEquals(5 - $offset, $this->out->tell());
  }

  #[@test]
  public function replacing_character() {
    $this->out->write('Hello');
    $this->out->seek(0, SEEK_SET);
    $this->out->write('h');
    $this->assertEquals('hello', $this->out->bytes());
  }

  #[@test]
  public function seeking_to_end() {
    $this->out->write('Hello');
    $this->out->seek(0, SEEK_END);
    $this->out->write('!');
    $this->assertEquals('Hello!', $this->out->bytes());
  }

  #[@test]
  public function seeking_to_end_minus_one() {
    $this->out->write('Hello');
    $this->out->seek(-1, SEEK_END);
    $this->out->write('_');
    $this->assertEquals('Hell_', $this->out->bytes());
  }

  #[@test]
  public function overwriting() {
    $this->out->write('Hello');
    $this->out->seek(1, SEEK_SET);
    $this->out->write('ai');
    $this->out->write('fisch');
    $this->assertEquals('Haifisch', $this->out->bytes());
  }

  #[@test]
  public function closing_twice_has_no_effect() {
    $this->out->close();
    $this->out->close();
  }
}
