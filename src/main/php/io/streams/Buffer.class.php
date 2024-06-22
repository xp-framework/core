<?php namespace io\streams;

use io\{File, Folder};
use lang\IllegalArgumentException;

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

  /** @return ?io.File */
  public function file() { return $this->file; }

  /**
   * Write a string
   *
   * @param  var $arg
   * @return void
   */
  public function write($bytes) {
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
   * Finish buffering
   *
   * @return void
   */
  public function finish() {
    $this->file && $this->file->seek(0, SEEK_SET);
  }

  /** @return int */
  public function available() {
    return $this->file ? $this->size - $this->file->tell() : strlen($this->memory ?? '');
  }

  /**
   * Read a string
   *
   * @param  int $limit
   * @return ?string
   */
  public function read($limit= 8192) {
    if ($this->file) {
      $chunk= $this->file->read($limit);
      return false === $chunk ? null : $chunk;
    }

    // Drain memory
    if (null === $this->memory) {
      $chunk= null;
    } else if ($limit >= strlen($this->memory)) {
      $chunk= $this->memory;
      $this->memory= null;
    } else {
      $chunk= substr($this->memory, 0, $limit);
      $this->memory= substr($this->memory, $limit);
    }
    return $chunk;
  }

  /** @return void */
  public function close() {
    if (null === $this->file || !$this->file->isOpen()) return;

    $this->file->close();
    $this->file->unlink();
  }

  /** Ensure file is closed */
  public function __destruct() {
    $this->close();
  }
}