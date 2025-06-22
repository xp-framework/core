<?php namespace io\streams;

use lang\IllegalArgumentException;

/**
 * Reads through all given input streams
 *
 * @test io.unittest.SequenceInputStreamTest
 */
class SequenceInputStream implements InputStream {
  private $streams, $current;

  /**
   * Creates a new instance
   *
   * @param  iterable|io.streams.InputStream... $args
   * @throws lang.IllegalArgumentException if streams are empty
   */
  public function __construct(... $args) {
    $this->streams= $this->iterator($args);
    if (!$this->streams->valid()) {
      throw new IllegalArgumentException('Streams may not be empty');
    }

    $this->current= $this->streams->current();
  }

  /** Creates an iterator from the given arguments */
  private function iterator($args) {
    foreach ($args as $arg) {
      if ($arg instanceof InputStream) {
        yield $arg;
      } else {
        yield from $arg;
      }
    }
  }

  /** @return int */
  public function available() {
    do {
      if ($r= $this->current->available()) return $r;

      // No more data available on current stream, close and select next
      $this->current->close();
      $this->streams->next();
    } while ($this->streams->valid() && ($this->current= $this->streams->current()));

    return 0;
  }

  /**
   * Reads up to the specified number of bytes
   *
   * @param  int $bytes
   * @return string
   */
  public function read($bytes= 8192) {
    do {
      if ('' !== ($r= $this->current->read($bytes))) return $r;

      // EOF from current stream, close and select next
      $this->current->close();
      $this->streams->next();
    } while ($this->streams->valid() && ($this->current= $this->streams->current()));

    return '';
  }

  /** @return void */
  public function close() {
    while ($this->streams->valid()) {
      $this->streams->current()->close();
      $this->streams->next();
    }
  }

  /** Ensure streams are closed */
  public function __destruct() {
    $this->close();
  }
}