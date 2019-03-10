<?php namespace io;

/**
 * File utility functions
 *
 * @see   xp://io.File
 * @test  xp://net.xp_framework.unittest.io.FileUtilTest
 */
class Files {

  /**
   * Retrieve file contents as a string. If the file was previously open,
   * it is not closed when EOF is reached.
   *
   * ```php
   * $str= Files::read(new File('/etc/passwd'));
   * ```
   *
   * @param  string|io.File $file
   * @return string file contents
   * @throws io.IOException
   * @throws io.FileNotFoundException
   */
  public static function read($file) {
    if ($file instanceof File) {
      if ($file->isOpen()) {
        $bytes= '';
        do {
          $bytes.= $file->read();
        } while (!$file->eof());
      } else {
        clearstatcache();
        $file->open(File::READ);
        $size= $file->size();

        // Read until EOF. Best case scenario is that this will run exactly once.
        $bytes= '';
        do {
          $l= $size - strlen($bytes);
          $bytes.= $file->read($l);
        } while ($l > 0 && !$file->eof());
        $file->close();
      }
      return $bytes;
    }
    return self::read(new File($file));
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
    if ($file instanceof File) {
      if ($file->isOpen()) {
        $file->seek(0, SEEK_SET);
        $written= $file->write($bytes);
        $file->truncate($written);
      } else {
        $file->open(File::WRITE);
        $written= $file->write($bytes);
        $file->close();
      }
      return $written;
    }
    return self::write(new File($file), $bytes);
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
    if ($file instanceof File) {
      if ($file->isOpen()) {
        $written= $file->write($bytes);
      } else {
        $file->open(File::APPEND);
        $written= $file->write($bytes);
        $file->close();
      }
      return $written;
    }
    return self::append(new File($file), $bytes);
  }
}
