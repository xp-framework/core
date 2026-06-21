<?php namespace io\streams;

use io\OperationFailed;
use lang\Environment;

/**
 * Seekable input stream which spools to a temporary file
 *
 * @test  io.unittest.SpooledInputStreamTest
 */
class SpooledInputStream implements InputStream, Seekable {
  const PREFIX= 'spooled-';

  private $in, $buffer;
  private $end= null;

  /**
   * Creates a new spooled input stream
   * 
   * @param  io.streams.InputStream $in
   * @param  ?string|io.Path|io.Folder|io.File|io.streams.Buffer $temp
   */
  public function __construct(InputStream $in, $temp= null) {
    $this->in= $in;
    $this->buffer= $temp instanceof Buffer ? $temp : new Buffer($temp ?? Environment::tempDir(), 0);
  }

  /** @return int */
  public function available() {
    return null === $this->end
      ? $this->in->available() + $this->buffer->size() - $this->buffer->tell()
      : $this->end - $this->buffer->tell()
    ;
  }

  /**
   * Reads, spooling the data to the file
   *
   * @param  int $limit
   * @return string
   */
  public function read($limit= 8192) {

    // Read from the underlying stream if we're at the end of the file
    if (null === $this->end && $this->buffer->tell() >= $this->buffer->size()) {
      $chunk= $this->in->read($limit);
      if ('' === $chunk) {
        $this->end= $this->buffer->tell();
      } else {
        $this->buffer->write($chunk);
      }
    } else {
      $chunk= $this->buffer->read($limit);
    }

    return $chunk;
  }

  /** @return int */
  private function drain() {
    $this->buffer->seek(0, SEEK_END);
    while ('' !== ($chunk= $this->in->read())) {
      $this->buffer->write($chunk);
    }
    return $this->buffer->tell();
  }

  /**
   * Seeks to a given offset
   *
   * @param  int $offset
   * @param  int $whence SEEK_SET, SEEK_CUR or SEEK_END
   * @return void
   * @throws io.OperationFailed
   */
  public function seek($offset, $whence= SEEK_SET) {
    switch ($whence) {
      case SEEK_SET: $position= $offset; break;
      case SEEK_CUR: $position= $this->buffer->tell() + $offset; break;
      case SEEK_END: $position= ($this->end??= $this->drain()) + $offset; break;
      default: $position= -1; break;
    }

    if ($position < 0) {
      throw new OperationFailed("Seek error, position {$offset} in mode {$whence}");
    }

    // Read from underlying stream when seeking forward, clamping on EOF.
    if (null === $this->end && ($fill= ($position - $this->buffer->size())) > 0) {
      $this->buffer->seek(0, SEEK_END);
      while ($fill > 0 && $this->in->available()) {
        $chunk= $this->in->read($fill);
        $this->buffer->write($chunk);
        $fill-= strlen($chunk);
      }
      $fill && $this->end= $this->buffer->tell();
    }

    $this->buffer->seek(min($this->end ?? $this->buffer->size(), $position), SEEK_SET);
  }

  /** @return int */
  public function tell() { return $this->buffer->tell(); }

  /** @return void */
  public function close() {
    $this->buffer->close();
  }

  /** Ensures close() is called */
  public function __destruct() {
    $this->close();
  }
}