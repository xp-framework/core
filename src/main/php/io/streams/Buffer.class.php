<?php namespace io\streams;

use io\{File, Folder, Path, TempFile, OperationFailed};
use lang\IllegalArgumentException;

/**
 * Buffers in memory up until a given threshold, using the file system once
 * it's exceeded.
 * 
 * @see   https://github.com/xp-forge/web/issues/118
 * @test  io.unittest.BufferTest
 */
class Buffer implements InputStream, OutputStream, Seekable {
  private $files, $threshold, $persist;
  private $memory= '';
  private $file= null;
  private $size= 0;
  private $pointer= 0;

  /**
   * Creates a new buffer
   *
   * @param  string|io.Folder|io.Path|io.File $files
   * @param  int $threshold
   * @param  bool $persist
   * @throws lang.IllegalArgumentException
   */
  public function __construct($files, int $threshold= 0, bool $persist= false) {
    if ($threshold < 0) {
      throw new IllegalArgumentException('Threshold must be >= 0');
    }

    $this->threshold= $threshold;
    $this->persist= $persist;

    if ($files instanceof File) {
      $this->files= fn() => $files;
    } else if ($files instanceof Path && $files->isFile()) {
      $this->files= fn() => $files->asFile();
    } else {
      $this->files= fn() => new TempFile("b{$this->threshold}", $files);
    }
  }

  /** Returns buffer size */
  public function size(): int { return $this->size; }

  /** Returns the underlying file, if any */
  public function file(): ?File { return $this->file; }

  /**
   * Write a string
   *
   * @param  string $bytes
   * @return void
   */
  public function write($bytes) {
    $length= strlen($bytes);

    if ($this->size + $length <= $this->threshold) {
      $tail= strlen($this->memory);
      if ($this->pointer < $tail) {
        $this->memory= substr_replace($this->memory, $bytes, $this->pointer, $length);
      } else if ($this->pointer > $tail) {
        $this->memory.= str_repeat("\x00", $this->pointer - $tail).$bytes;
      } else {
        $this->memory.= $bytes;
      }

      $this->pointer+= $length;
      $this->size= strlen($this->memory);
    } else {
      if (null === $this->file) {
        $this->file= ($this->files)();
        $this->file->open(File::REWRITE);
        $this->file->write($this->memory);
        $this->file->seek($this->pointer, SEEK_SET);
        $this->memory= null;
      }

      $this->file->write($bytes);
      $this->size= $this->file->size();
    }
  }

  /** @return void */
  public function flush() {
    $this->file && $this->file->flush();
  }

  /** @return int */
  public function available() {
    return $this->size - ($this->file ? $this->file->tell() : $this->pointer);
  }

  /**
   * Read a string
   *
   * @param  int $limit
   * @return string
   */
  public function read($limit= 8192) {
    if ($this->file) {
      return (string)$this->file->read($limit);
    } else {
      $chunk= substr($this->memory, $this->pointer, $limit);
      $this->pointer+= strlen($chunk);
      return $chunk;
    }
  }

  /**
   * Resets buffer to be able to read from the beginning. Optimized
   * form of calling `seek(0, SEEK_SET)`.
   *
   * @return  void
   */
  public function reset() {
    $this->file ? $this->file->seek(0, SEEK_SET) : $this->pointer= 0;
  }

  /**
   * Seeks to a given offset.
   *
   * @param  int $offset
   * @param  int $whence SEEK_SET, SEEK_CUR or SEEK_END
   * @return void
   * @throws io.OperationFailed
   */
  public function seek($offset, $whence= SEEK_SET) {
    switch ($whence) {
      case SEEK_SET: $position= $offset; break;
      case SEEK_CUR: $position= ($this->file ? $this->file->tell() : $this->pointer) + $offset; break;
      case SEEK_END: $position= $this->size + $offset; break;
      default: $position= -1; break;
    }

    if ($position < 0) {
      throw new OperationFailed("Seek error, position {$offset} in mode {$whence}");
    }

    $this->file ? $this->file->seek($position, SEEK_SET) : $this->pointer= $position;
  }

  /** @return int */
  public function tell() {
    return $this->file ? $this->file->tell() : $this->pointer;
  }

  /** @return void */
  public function close() {
    if (null === $this->file || !$this->file->isOpen()) return;

    $this->file->close();
    $this->persist || ($this->file->exists() && $this->file->unlink());
  }

  /** Ensure the file (if any) is closed */
  public function __destruct() {
    $this->close();
  }
}