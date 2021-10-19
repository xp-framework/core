<?php namespace io;

/**
 * File utility functions
 *
 * @see   io.File
 * @test  net.xp_framework.unittest.io.FilesTest
 */
class Files {

  /**
   * Retrieve file contents as a string. If the file was previously open,
   * it is not closed when EOF is reached.
   *
   * ```php
   * $bytes= Files::read(new File('/etc/passwd'));
   * ```
   *
   * @param  string|io.File $file
   * @return string file contents
   * @throws io.IOException
   * @throws io.FileNotFoundException
   */
  public static function read($file) {
    $f= $file instanceof File ? $file : new File($file);

    if ($f->isOpen()) {
      $f->seek(0, SEEK_SET);
      $bytes= '';
      do {
        $bytes.= $f->read();
      } while (!$f->eof());
    } else {
      clearstatcache();
      $f->open(File::READ);
      $size= $f->size();

      // Read until EOF. Best case scenario is that this will run exactly once.
      $bytes= '';
      do {
        $l= $size - strlen($bytes);
        $bytes.= $f->read($l);
      } while ($l > 0 && !$f->eof());
      $f->close();
    }
    return $bytes;
  }

  /**
   * Set file contents. If the file was previously open, it is not closed
   * after the bytes have been written.
   *
   * ```php
   * $written= Files::write(new File('myfile'), 'Hello world');
   * ```
   *
   * @param  string|io.File $file
   * @param  string $bytes
   * @return int
   * @throws io.IOException
   */
  public static function write($file, $bytes) {
    $f= $file instanceof File ? $file : new File($file);
    if ($f->isOpen()) {
      $f->seek(0, SEEK_SET);
      $written= $f->write($bytes);
      $f->truncate($written);
    } else {
      $f->open(File::WRITE);
      $written= $f->write($bytes);
      $f->close();
    }
    return $written;
  }

  /**
   * Append file contents. If the file was previously open, it is not closed
   * after the bytes have been written.
   *
   * @param  string|io.File $file
   * @param  string $bytes
   * @return int
   * @throws io.IOException
   */
  public static function append($file, $bytes) {
    $f= $file instanceof File ? $file : new File($file);
    if ($f->isOpen()) {
      $written= $f->write($bytes);
    } else {
      $f->open(File::APPEND);
      $written= $f->write($bytes);
      $f->close();
    }
    return $written;
  }
}
