<?php namespace io\streams;

use io\{File, Folder};
use lang\{IllegalArgumentException, IllegalStateException};

/**
 * Buffers in memory up until a given threshold, using the file system once
 * it's exceeded.
 * 
 * @see   https://github.com/xp-forge/web/issues/118
 * @test  io.unittest.BufferTest
 */
class Buffer implements InputStream, OutputStream {
  private $files, $threshold;
  private $memory= '';
  private $file= null;
  private $size= 0;
  private $pointer= 0;
  private $draining= false;

  /**
   * Creates a new buffer
   *
   * @param  io.Folder|io.Path|string $files
   * @param  int $threshold
   * @throws lang.IllegalArgumentException
   */
  public function __construct($files, int $threshold) {
    if ($threshold < 0) {
      throw new IllegalArgumentException('Threshold must be >= 0');
    }

    $this->files= $files instanceof Folder ? $files->getURI() : (string)$files;
    $this->threshold= $threshold;
  }

  /** Returns buffer size */
  public function size(): int { return $this->size; }

  /** Returns the underlying file, if any */
  public function file(): ?File { return $this->file; }

  /** Returns whether this buffer is draining */
  public function draining(): bool { return $this->draining; }

  /**
   * Write a string
   *
   * @param  var $arg
   * @return void
   * @throws lang.IllegalStateException
   */
  public function write($bytes) {
    if ($this->draining) throw new IllegalStateException('Started draining buffer');

    $this->size+= strlen($bytes);
    if ($this->size <= $this->threshold) {
      $this->memory.= $bytes;
      return;
    }

    if (null === $this->file) {
      $this->file= new File(tempnam($this->files, "b{$this->threshold}"));
      $this->file->open(File::READWRITE);
      $this->file->write($this->memory);
      $this->memory= null;
    }
    $this->file->write($bytes);
  }

  /** @return void */
  public function flush() {
    $this->file && $this->file->flush();
  }

  /**
   * Resets buffer to be able to read from the beginning
   *
   * @return  void
   */
  public function reset() {
    $this->file ? $this->file->seek(0, SEEK_SET) : $this->pointer= 0;
    $this->draining= true;
  }

  /** @return int */
  public function available() {
    return $this->draining
      ? $this->size - ($this->file ? $this->file->tell() : $this->pointer)
      : $this->size
    ;
  }

  /**
   * Read a string
   *
   * @param  int $limit
   * @return ?string
   */
  public function read($limit= 8192) {
    if ($this->file) {
      $this->draining || $this->file->seek(0, SEEK_SET) && $this->draining= true;
      $chunk= $this->file->read($limit);
      return false === $chunk ? null : $chunk;
    } else if ($this->pointer < $this->size) {
      $this->draining= true;
      $chunk= substr($this->memory, $this->pointer, $limit);
      $this->pointer+= strlen($chunk);
      return $chunk;
    } else {
      $this->draining= true;
      return null;
    }
  }

  /** @return void */
  public function close() {
    if (null === $this->file || !$this->file->isOpen()) return;

    $this->file->close();
    $this->file->unlink();
  }

  /** Ensure the file (if any) is closed */
  public function __destruct() {
    $this->close();
  }
}